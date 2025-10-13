<?php
// ==== Konfigurasi ====
$TO_EMAIL = "admin@rproduction.site";
$SUBJECT_PREFIX = "[R Production Site] ";

// ==== Helper: Sanitasi sederhana ====
function clean($str) {
  $str = trim($str);
  // hapus karakter berbahaya untuk header
  $str = str_replace(array("\r","\n","%0a","%0d"), ' ', $str);
  return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// ==== Cek method ====
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: index.html");
  exit;
}

// ==== Honeypot anti-spam ====
if (!empty($_POST['website'])) {
  // bot terdeteksi
  header("Location: index.html");
  exit;
}

// ==== Ambil & validasi input ====
$name    = isset($_POST['name']) ? clean($_POST['name']) : '';
$email   = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL) : false;
$subject = isset($_POST['subject']) ? clean($_POST['subject']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

if (!$name || !$email || !$subject || strlen($message) < 5) {
  // Bisa diarahkan ke halaman error khusus
  echo "<script>alert('Mohon lengkapi form dengan benar.'); history.back();</script>";
  exit;
}

// ==== Susun email ====
$subject_full = $SUBJECT_PREFIX . $subject;

$body_lines = [
  "Nama   : {$name}",
  "Email  : {$email}",
  "Subjek : {$subject}",
  "----- Pesan -----",
  $message
];
$body = implode("\n", $body_lines);

// Header
$headers = "From: {$name} <{$email}>\r\n";
$headers .= "Reply-To: {$email}\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// ==== Kirim ====
$sent = @mail($TO_EMAIL, $subject_full, $body, $headers);

// ==== Redirect ====
if ($sent) {
  // ke halaman thank you
  header("Location: thankyou.html");
  exit;
} else {
  // fallback: kembali ke index dengan notif sederhana
  header("Location: index.html?sent=1");
  exit;
}
