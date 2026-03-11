<?php
$title = $title ?? 'Accounts App';
$heading = $heading ?? '';
?>

<!DOCTYPE html>
<html>
<head>

    <meta charset="utf8mb4">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">

    <title><?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="../style.css" media="screen">
    <link rel="icon" type="image/x-icon" href="../favicon.ico">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        $(document).ready(function () {
            $("tr:even").css("background-color", "#6e95f0");
        });
    </script>

</head>
<body>
<?php $uri = parse_url($_SERVER['REQUEST_URI'])['path']; ?>

<div class="nav-container">

    <nav class="navbar">
        <ul class="flex-container">
            <li><a href="/transactions/create" class="<?= ($uri === '/' || $uri === '/transactions/create') ? 'activePage' : ''; ?>">New Transaction</a></li>
            <li><a href="/accounts"
                   class="<?= ($uri === '/accounts' || $uri === '/accounts/show') ? 'activePage' : ''; ?>">Accounts</a>
            </li>
            <li><a href="/transactions"
                   class="<?= ($uri === '/transactions' || $uri === '/transactions/show' || $uri === '/transactions/edit' || $uri === '/transactions/delete') ? 'activePage' : ''; ?>">Transactions</a>
            </li>
            <li><a href="/accounts/balances"
                   class="<?= $uri === '/accounts/balances' ? 'activePage' : ''; ?>">Balances</a>
            </li>

            <!-- Manage dropdown -->
            <li class="dropdown-container">
                <span class="dropdown-toggle">Manage</span> <!-- No link behavior -->

                <ul class="dropdown">
                    <li><a href="/reconcile" class="<?= $uri === '/reconcile' ? 'activePage' : ''; ?>">Reconcile</a></li>
                    <li><a href="/accounts/overview" class="<?= $uri === '/accounts/overview' ? 'activePage' : ''; ?>">Accounts
                            Overview</a></li>
                    <li><a href="/payees/payee"
                           class="<?= $uri === '/payees/payee' || $uri === '/payees/payee_create' ? 'activePage' : ''; ?>">Payees</a>
                    </li>
                    <li><a href="/categories/category"
                           class="<?= $uri === '/categories/category' || $uri === '/categories/category_create' ? 'activePage' : ''; ?>">Categories</a>
                    </li>
                    <li><a href="/types/type" class="<?= $uri === '/types/type' ? 'activePage' : ''; ?>">Transaction
                            Types</a></li>
                    <li><a href="/creditcards/creditcard"
                           class="<?= $uri === '/creditcards/creditcard' ? 'activePage' : ''; ?>">Credit Cards</a></li>
                </ul>
            </li>
        </ul>
    </nav>

</div>


<?php if ($heading !== ''): ?>
    <h2><?= htmlspecialchars($heading) ?></h2>
<?php endif; ?>




