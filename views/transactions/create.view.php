<?php partial('header', ['title' => ($title ?? 'Accounts App'), 'heading' => ($heading ?? '')]); ?>

<div>
    <form action="/transactions/store" method="POST">
        <input type="date" id="transaction_date" name="transaction_date" required>

        <select name="rpaccount_id" id="rpaccount_id" required>
            <option class="select" disabled selected hidden value="">Select account...</option>
            <?php foreach ($accounts as $account): ?>
                <option value="<?= (int) $account['id']; ?>">
                    <?= htmlspecialchars($account['account_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="type_id" id="type_id" required>
            <option class="select" disabled selected hidden value="">Select transaction type...</option>
            <?php foreach ($types as $type): ?>
                <option value="<?= (int) $type['id']; ?>">
                    <?= htmlspecialchars($type['type_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="payee_id" id="payee_id" required>
            <option class="select" disabled selected hidden value="">Select payee...</option>
            <?php foreach ($payees as $payee): ?>
                <option value="<?= (int) $payee['id']; ?>">
                    <?= htmlspecialchars($payee['payee_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="category_id" id="category_id" required>
            <option class="select" disabled selected hidden value="">Select category...</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= (int) $category['id']; ?>">
                    <?= htmlspecialchars($category['category_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <div id="cc-field" style="display:none; margin-top:1em;">
            <select name="credit_card_id" id="credit_card_id">
                <option disabled selected hidden value="">Select credit card...</option>
                <?php foreach ($creditCards as $cc): ?>
                    <option value="<?= (int) $cc['credit_card_id']; ?>">
                        <?= htmlspecialchars($cc['account_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <input type="number" id="amount" name="amount" step="0.01" placeholder="Enter transaction amount..." required>

        <br><br>
        <input type="submit" name="submit" value="Create Transaction">
    </form>
</div>

<?php partial('footer'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const typeSelect = document.getElementById('type_id');
        const categorySelect = document.getElementById('category_id');
        const ccField = document.getElementById('cc-field');
        const ccSelect = document.getElementById('credit_card_id');

        function toggleCCField() {
            if (typeSelect.value === '18' && categorySelect.value === '23') {
                ccField.style.display = 'block';
                ccSelect.required = true;
            } else {
                ccField.style.display = 'none';
                ccSelect.required = false;
                ccSelect.value = '';
            }
        }

        typeSelect.addEventListener('change', toggleCCField);
        categorySelect.addEventListener('change', toggleCCField);
        toggleCCField();
    });
</script>

