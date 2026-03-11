<?php
$title = 'Credit Cards';
$heading = 'Credit Cards';
partial('header', ['title' => ($title ?? 'Accounts App'), 'heading' => ($heading ?? '')]);
?>

<?php if (!empty($creditCards)): ?>

    <div class="table-wrapper">
        <table>
            <thead>
            <tr>
                <th>Card ID</th>
                <th>Account Name</th> <!-- ✅ Updated -->
                <th>Balance</th>
                <th>Credit Limit</th>
                <th>Available Credit</th>
                <th>Payment Due Date</th>
                <th>Statement Closing Date</th>
                <th>Utilization Percentage</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($creditCards as $card): ?>
                <tr class="text-last-column">
                    <td><?= htmlspecialchars($card->id); ?></td>
                    <td><?= htmlspecialchars($card->account_name); ?></td> <!-- ✅ Updated -->
                    <td><?= htmlspecialchars(number_format($card->balance, 2)); ?></td>
                    <td><?= htmlspecialchars(number_format($card->credit_limit, 2)); ?></td>
                    <td><?= htmlspecialchars(number_format($card->available_credit, 2)); ?></td>
                    <td class="date-due"><?= htmlspecialchars($card->due_date); ?></td>
                    <td class="closing-date"><?= htmlspecialchars($card->closing_date); ?></td>
                    <td class="utilization"><?= htmlspecialchars($card->utilization_percentage); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php else: ?>
    <p>No credit cards found.</p>
<?php endif; ?>

<?php partial('footer'); ?>

<script>
    document.querySelectorAll('.utilization').forEach(cell => {
        let value = parseFloat(cell.textContent);
        if (value >= 30) {
            cell.classList.add('high-utilization');
        }
    });

    // Current Date
    const today = new Date();

    // Select all cells with the class "date-due" and "closing-date"
    const dueDates = document.querySelectorAll('.date-due');
    const closingDates = document.querySelectorAll('.closing-date');

    // Loop through and apply conditional formatting
    dueDates.forEach(cell => {
        const dueDate = new Date(cell.textContent.trim()); // Parse the date in the cell
        if (!isNaN(dueDate)) { // Check if the date is valid
            const diffInDays = Math.round((dueDate - today) / (1000 * 60 * 60 * 24)); // Rounded difference in days
            if (diffInDays === 2) {
                cell.style.color = 'green'; // Make text green
                cell.style.fontWeight = 'bold'; // Optional: Bold the text
            }
        }
    });

    closingDates.forEach(cell => {
        const closingDate = new Date(cell.textContent.trim() + "T00:00:00"); // Normalize date parsing
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
