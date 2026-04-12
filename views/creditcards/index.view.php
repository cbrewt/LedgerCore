<?php
$title = 'Credit Cards';
$heading = 'Credit Cards';
partial('header', ['title' => ($title ?? 'Accounts App'), 'heading' => ($heading ?? '')]);
?>

<div class="credit-cards-page">
    <?php if (!empty($creditCards)): ?>

        <div class="table-wrapper credit-cards-table-wrapper">
            <table class="credit-cards-table">
                <thead>
                <tr>
                    <th>Card ID</th>
                    <th>Account Name</th>
                    <th>Balance</th>
                    <th>Credit Limit</th>
                    <th>Available Credit</th>
                    <th>Payment Due Date</th>
                    <th>Statement Closing Date</th>
                    <th>Utilization %</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($creditCards as $card): ?>
                    <tr class="text-last-column">
                        <td><?= htmlspecialchars((string) $card->id); ?></td>
                        <td><?= htmlspecialchars((string) $card->account_name); ?></td>
                        <td><?= htmlspecialchars(number_format((float) $card->balance, 2)); ?></td>
                        <td><?= htmlspecialchars(number_format((float) $card->credit_limit, 2)); ?></td>
                        <td><?= htmlspecialchars(number_format((float) $card->available_credit, 2)); ?></td>
                        <td class="date-due"><?= htmlspecialchars((string) $card->due_date); ?></td>
                        <td class="closing-date"><?= htmlspecialchars((string) $card->closing_date); ?></td>
                        <td class="utilization"><?= htmlspecialchars((string) $card->utilization_percentage); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>
        <p>No credit cards found.</p>
    <?php endif; ?>
</div>

<style>
.credit-cards-page {
    width: 100%;
}

.credit-cards-table-wrapper {
    max-width: 1250px;
    margin: 0 auto;
    overflow-x: visible;
}

.credit-cards-table {
    width: 100%;
    margin: 0 auto;
    table-layout: auto;
    font-size: 1.05em;
}

.credit-cards-table thead th {
    padding: 10px 8px;
    white-space: nowrap;
    font-size: 0.95em;
}

.credit-cards-table td {
    padding: 12px 8px;
    white-space: normal;
}

.credit-cards-table td:nth-child(2),
.credit-cards-table th:nth-child(2) {
    width: 220px;
}

.credit-cards-table td:last-child,
.credit-cards-table th:last-child {
    min-width: 110px;
    text-align: center;
}

.credit-cards-table td:last-child::after {
    content: none;
}
</style>

<?php partial('footer'); ?>

<script>
    document.querySelectorAll('.utilization').forEach(cell => {
        let value = parseFloat(cell.textContent);
        if (value >= 30) {
            cell.classList.add('high-utilization');
        }
    });

    const today = new Date();
    const dueDates = document.querySelectorAll('.date-due');
    const closingDates = document.querySelectorAll('.closing-date');

    dueDates.forEach(cell => {
        const dueDate = new Date(cell.textContent.trim());
        if (!isNaN(dueDate)) {
            const diffInDays = Math.round((dueDate - today) / (1000 * 60 * 60 * 24));
            if (diffInDays === 2) {
                cell.style.color = 'green';
                cell.style.fontWeight = 'bold';
            }
        }
    });

    closingDates.forEach(cell => {
        const closingDate = new Date(cell.textContent.trim() + "T00:00:00");
        if (!isNaN(closingDate)) {
            if (
                closingDate.getFullYear() === today.getFullYear() &&
                closingDate.getMonth() === today.getMonth() &&
                closingDate.getDate() === today.getDate()
            ) {
                cell.classList.add('closing-today');
            }
        }
    });
</script>