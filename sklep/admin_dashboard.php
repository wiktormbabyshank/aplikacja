<?php
session_start(); 

if (!isset($_SESSION['admin_email'])) {
    header("Location: index.html"); 
    exit();
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administracyjny</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container1">
        <h1>Panel Administracyjny</h1>
        <nav>
            <ul>
                <li><a href="#products">Zarządzaj produktami</a></li>
                <li><a href="#orders">Zarządzaj zamówieniami</a></li>
                <li><a href="#users">Zarządzaj użytkownikami</a></li>
            </ul>
        </nav>

        <form action="logout.php" method="post">
            <button type="submit">Wyloguj się</button>
        </form>

        <section id="products">
            <h2>Zarządzaj produktami</h2>
            <form action="add_product.html" method="get">
                <button type="submit">Dodaj nowy produkt</button>
            </form>
            <table>
                <thead>
                    <tr>
                        <th>Nazwa</th>
                        <th>Cena</th>
                        <th>Ilość</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Produkt 1</td>
                        <td>100 PLN</td>
                        <td>10</td>
                        <td><button>Edytuj</button> <button>Usuń</button></td>
                    </tr>
                    <tr>
                        <td>Produkt 2</td>
                        <td>200 PLN</td>
                        <td>5</td>
                        <td><button>Edytuj</button> <button>Usuń</button></td>
                    </tr>
                </tbody>
            </table>
        </section>

        <section id="orders">
            <h2>Zarządzaj zamówieniami</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID Zamówienia</th>
                        <th>Użytkownik</th>
                        <th>Kwota</th>
                        <th>Status</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Użytkownik 1</td>
                        <td>150 PLN</td>
                        <td>W trakcie realizacji</td>
                        <td><button>Edytuj</button> <button>Usuń</button></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Użytkownik 2</td>
                        <td>300 PLN</td>
                        <td>Zrealizowane</td>
                        <td><button>Edytuj</button> <button>Usuń</button></td>
                    </tr>
                </tbody>
            </table>
        </section>

        <section id="users">
            <h2>Zarządzaj użytkownikami</h2>
            <table>
                <thead>
                    <tr>
                        <th>Imię i Nazwisko</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Użytkownik 1</td>
                        <td>u1@example.com</td>
                        <td>Aktywny</td>
                        <td><button>Edytuj</button> <button>Usuń</button></td>
                    </tr>
                    <tr>
                        <td>Użytkownik 2</td>
                        <td>u2@example.com</td>
                        <td>Zablokowany</td>
                        <td><button>Edytuj</button> <button>Usuń</button></td>
                    </tr>
                </tbody>
            </table>
        </section>
    </div>
    <form action="logout.php" method="post">
    <button type="submit">Wyloguj się</button>
    </form>
</body>
</html>
