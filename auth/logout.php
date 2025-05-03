<?php
session_start();
session_destroy();
header('Location: /myschoolface/auth/login');
exit();
