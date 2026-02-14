<?php
session_start();
require_once __DIR__ . '/../app/auth.php';

require_role('admin');

echo "Admin-only OK.";
