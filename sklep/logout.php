<?php
session_start(); // Rozpocznij sesję
session_unset(); // Usunięcie wszystkich zmiennych sesyjnych
session_destroy(); // Zniszczenie sesji
header("Location: index.html"); // Przekierowanie do strony logowania
exit();
?>
