<?php
$dbFile = 'C:/Users/aaron/web-prgramming/campus-marketplace/database/campus_marketplace.db';
$pdo = new PDO('sqlite:' . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec("
CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  full_name TEXT NOT NULL,
  college_email TEXT NOT NULL UNIQUE,
  student_id TEXT NOT NULL UNIQUE,
  password_hash TEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS products (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  seller_id INTEGER NOT NULL,
  title TEXT NOT NULL,
  description TEXT NOT NULL,
  price REAL NOT NULL,
  product_data TEXT NOT NULL,
  image_path TEXT DEFAULT NULL,
  status TEXT NOT NULL DEFAULT 'Available',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  archived_at DATETIME DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS offers (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  product_id INTEGER NOT NULL,
  buyer_id INTEGER NOT NULL,
  seller_id INTEGER NOT NULL,
  parent_offer_id INTEGER DEFAULT NULL,
  offered_price REAL NOT NULL,
  message TEXT DEFAULT NULL,
  status TEXT NOT NULL DEFAULT 'Pending',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
");

$count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
if ($count == 0) {
    $hash = password_hash('Password123', PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (full_name, college_email, student_id, password_hash) VALUES (?, ?, ?, ?)");
    $stmt->execute(['Aarav Sharma', 'aarav.sharma@campus.edu', 'STU2024001', $hash]);
    $stmt->execute(['Priya Patel', 'priya.patel@campus.edu', 'STU2024002', $hash]);
    $stmt->execute(['Rahul Mehra', 'rahul.mehra@campus.edu', 'STU2024003', $hash]);

    $products = [
      [1, 'Database Management Systems — Navathe (4th Ed.)', 'Comprehensive DBMS textbook by Elmasri & Navathe. Some highlighting in chapters 3-7 but otherwise in great shape. Perfect for CSE students.', 450.00, '{"category":"Textbooks and Academic Materials","condition":"Good","specifications":{"subject":"Database Management Systems","edition":"Fourth Edition","language":"English","author":"Elmasri & Navathe"}}', null, 'Available'],
      [1, 'Casio FX-991EX Scientific Calculator', 'Advanced scientific calculator with spreadsheet, statistics, and matrix functions. Battery still holds charge. Comes with original case.', 800.00, '{"category":"Electronics and Gadgets","condition":"Like New","specifications":{"brand":"Casio","model":"FX-991EX","type":"Scientific Calculator"}}', null, 'Available'],
      [2, 'JBL Tune 510BT Wireless Headphones', 'Over-ear Bluetooth headphones with 40-hour battery life. Used for one semester. Minor wear on the headband cushion.', 1200.00, '{"category":"Electronics and Gadgets","condition":"Good","specifications":{"brand":"JBL","model":"Tune 510BT","connectivity":"Bluetooth 5.0","battery":"40 hours"}}', null, 'Available'],
      [2, 'Wooden Study Table with Drawer', 'Compact study table ideal for hostel rooms. Includes a single drawer for stationery. Dimensions: 90cm x 60cm x 75cm.', 2500.00, '{"category":"Furniture","condition":"Fair","specifications":{"material":"Engineered Wood","dimensions":"90x60x75 cm","color":"Walnut"}}', null, 'Available'],
      [3, 'Engineering Drawing Kit — Complete Set', 'Full drawing set including mini-drafter, compass, set squares, protractor, and French curves. Used for ED course.', 350.00, '{"category":"Textbooks and Academic Materials","condition":"Good","specifications":{"contents":"Mini-drafter, Compass, Set Squares, Protractor, French Curves","brand":"Rotring"}}', null, 'Available'],
      [3, 'Logitech B175 Wireless Mouse', 'Reliable wireless mouse with 12-month battery life. USB nano receiver included. Great for presentations and labs.', 400.00, '{"category":"Electronics and Gadgets","condition":"Like New","specifications":{"brand":"Logitech","model":"B175","connectivity":"2.4 GHz Wireless","battery":"12 months"}}', null, 'Available'],
      [1, 'Yonex Badminton Racquet — Nanoray 10F', 'Lightweight badminton racquet suitable for beginners and intermediate players. Comes with a half cover.', 600.00, '{"category":"Sports Equipment","condition":"Good","specifications":{"brand":"Yonex","model":"Nanoray 10F","weight":"Lightweight","skill_level":"Beginner-Intermediate"}}', null, 'Available'],
      [2, 'Electric Kettle — 1.5L', 'Stainless steel electric kettle, perfect for making tea, coffee, or instant noodles in the hostel. Auto shut-off feature.', 500.00, '{"category":"Hostel Essentials","condition":"Good","specifications":{"capacity":"1.5 Litres","material":"Stainless Steel","power":"1500W","features":"Auto shut-off, Boil-dry protection"}}', null, 'Available'],
      [3, 'Formal White Shirt — Size M', 'Cotton formal shirt worn only twice for presentations. No stains or wrinkles. Brand: Van Heusen.', 300.00, '{"category":"Clothing and Accessories","condition":"Like New","specifications":{"brand":"Van Heusen","size":"Medium","color":"White","material":"Cotton"}}', null, 'Available'],
      [1, 'USB-C Hub — 7-in-1 Adapter', 'Multiport USB-C hub with HDMI, USB 3.0 x2, SD card reader, micro-SD, USB-C PD pass-through. Works with all laptops.', 900.00, '{"category":"Electronics and Gadgets","condition":"Like New","specifications":{"brand":"Anker","ports":"HDMI, USB 3.0 x2, SD, microSD, USB-C PD","compatibility":"Universal USB-C"}}', null, 'Available'],
      [2, 'Desk Lamp with Clamp — LED', 'Adjustable LED desk lamp with flexible arm and desk clamp. Three brightness levels. USB powered.', 350.00, '{"category":"Hostel Essentials","condition":"Good","specifications":{"type":"LED","mounting":"Clamp","brightness_levels":3,"power":"USB"}}', null, 'Available'],
      [3, 'Data Structures and Algorithms Made Easy — Narasimha Karumanchi', 'Popular DSA book for placement preparation. Some pencil notes in margins, easily erasable.', 250.00, '{"category":"Textbooks and Academic Materials","condition":"Fair","specifications":{"subject":"Data Structures & Algorithms","author":"Narasimha Karumanchi","language":"English"}}', null, 'Available']
    ];

    $pStmt = $pdo->prepare("INSERT INTO products (seller_id, title, description, price, product_data, image_path, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($products as $p) {
        $pStmt->execute($p);
    }

    $oStmt = $pdo->prepare("INSERT INTO offers (product_id, buyer_id, seller_id, parent_offer_id, offered_price, message, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $oStmt->execute([1, 2, 1, null, 350.00, 'Hi! Would you accept ?350 for the DBMS book? I can pick it up today.', 'Pending']);
    $oStmt->execute([3, 1, 2, null, 900.00, 'I am interested in the JBL headphones. Would ?900 work?', 'Countered']);
    $oStmt->execute([4, 1, 2, null, 2000.00, 'Is the study table still available? Can you do ?2000?', 'Pending']);
    $oStmt->execute([3, 1, 2, 2, 1050.00, 'How about ?1050? That is the lowest I can go.', 'Pending']);
}
echo 'SQLite database initialized successfully!';
