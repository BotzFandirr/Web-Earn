<?php
require_once __DIR__ . '/lib/auth.php';

session_unset();
session_destroy();

session_start();
flash_set('success', 'Berhasil logout.');
redirect('/login.php');
