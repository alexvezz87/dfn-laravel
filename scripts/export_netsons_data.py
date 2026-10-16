import ftplib
import urllib.request
import ssl
import json
import os
import sys

PROD_CREDS = {
    'host': '89.40.173.89',
    'user': 'upload@dfnprenotazioni.it',
    'pass': 'NwAyA}@+uDt4',
    'url': 'https://www.dfnprenotazioni.it/'
}

SECRET_TOKEN = 'sync_token_alex_20261016_dfn'
TEMP_FILE = 'web/dfn_export_temp_sync.php'
REMOTE_FILENAME = 'dfn_export_temp_sync.php'

PHP_CODE = f"""<?php
error_reporting(0);
ini_set('display_errors', 0);

$token = $_GET['token'] ?? '';
if ($token !== '{SECRET_TOKEN}') {{
    http_response_code(403);
    exit('Forbidden');
}}

require_once __DIR__ . '/wp/wp-load.php';
global $wpdb;

header('Content-Type: application/json; charset=utf-8');

$prefix = $wpdb->prefix;

$events = $wpdb->get_results("SELECT * FROM {{$prefix}}dfn_events", ARRAY_A);
$slots = $wpdb->get_results("SELECT * FROM {{$prefix}}dfn_event_slots", ARRAY_A);
$bookings = $wpdb->get_results("SELECT * FROM {{$prefix}}dfn_bookings", ARRAY_A);
$fai_members = $wpdb->get_results("SELECT * FROM {{$prefix}}dfn_fai_members", ARRAY_A);

// Recupera i titoli dei prodotti woocommerce collegati agli eventi
$product_ids = [];
if (!empty($events)) {{
    foreach ($events as $e) {{
        if (!empty($e['product_id'])) {{
            $product_ids[] = (int)$e['product_id'];
        }}
    }}
}}

$products_map = [];
if (!empty($product_ids)) {{
    $ids_in = implode(',', array_unique($product_ids));
    $posts = $wpdb->get_results("SELECT ID, post_title FROM {{$wpdb->posts}} WHERE ID IN ($ids_in)", ARRAY_A);
    foreach ($posts as $p) {{
        $products_map[$p['ID']] = $p['post_title'];
    }}
}}

echo json_encode([
    'success' => true,
    'events' => $events,
    'slots' => $slots,
    'bookings' => $bookings,
    'fai_members' => $fai_members,
    'products_map' => $products_map
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
exit;
"""

def main():
    print("1. Connessione FTP a Netsons...")
    ftp = ftplib.FTP(PROD_CREDS['host'], timeout=15)
    ftp.login(PROD_CREDS['user'], PROD_CREDS['pass'])
    
    print(f"2. Caricamento file temporaneo {TEMP_FILE}...")
    import io
    bio = io.BytesIO(PHP_CODE.encode('utf-8'))
    ftp.storbinary(f'STOR {TEMP_FILE}', bio)
    
    url = f"{PROD_CREDS['url']}{REMOTE_FILENAME}?token={SECRET_TOKEN}"
    print(f"3. Download dati da {url}...")
    ctx = ssl.create_default_context()
    ctx.check_hostname = False
    ctx.verify_mode = ssl.CERT_NONE
    
    req = urllib.request.Request(url, headers={'User-Agent': 'DFN-Sync/1.0'})
    response = urllib.request.urlopen(req, context=ctx, timeout=30)
    raw_data = response.read().decode('utf-8')
    
    print(f"4. Rimozione file temporaneo {TEMP_FILE} su FTP...")
    try:
        ftp.delete(TEMP_FILE)
    except Exception as e:
        print("Errore eliminazione:", e)
    ftp.quit()
    
    data = json.loads(raw_data)
    os.makedirs('storage/sync', exist_ok=True)
    out_path = 'storage/sync/netsons_prod_data.json'
    with open(out_path, 'w', encoding='utf-8') as f:
        json.dump(data, f, ensure_ascii=False, indent=2)
        
    print(f"SUCCESS! Dati salvati in {out_path}")
    print(f"Eventi trovati: {len(data.get('events', []))}")
    print(f"Slot trovati: {len(data.get('slots', []))}")
    print(f"Prenotazioni trovate: {len(data.get('bookings', []))}")
    print(f"Soci FAI trovati: {len(data.get('fai_members', []))}")
    print("Eventi:")
    for ev in data.get('events', []):
        prod_title = data.get('products_map', {}).get(str(ev.get('product_id')), '')
        print(f" - ID {ev.get('id')}: {prod_title or ev.get('title')} ({ev.get('event_date_start')})")

if __name__ == '__main__':
    main()
