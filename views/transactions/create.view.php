<?php partial('header', ['title' => ($title ?? 'Accounts App'), 'heading' => ($heading ?? '')]); ?>

<div class="transaction-create-page">
    <div class="transaction-create-card">
        <form action="/transactions/store" method="POST" class="transaction-create-form">
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

            <div id="cc-field" class="transaction-create-cc-field" style="display:none;">
                <select name="credit_card_id" id="credit_card_id">
                    <option disabled selected hidden value="">Select credit card...</option>
                    <?php foreach ($creditCards as $cc): ?>
                        <option value="<?= (int) $cc['credit_card_id']; ?>">
                            <?= htmlspecialchars($cc['account_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <input
                type="number"
                id="amount"
                name="amount"
                step="0.01"
                placeholder="Enter transaction amount..."
                required
            >

            <input
                type="submit"
                name="submit"
                value="Create Transaction"
                class="transaction-create-submit"
            >
        </form>
    </div>
</div>

<style>
.transaction-create-page {
    display: flex;
    justify-content: center;
    padding: 28px 20px 56px;
}

.transaction-create-card {
    width: 100%;
    max-width: 620px;
    background: #f8faff;
    border: 1px solid #dbe4ff;
    border-radius: 16px;
    box-shadow: 0 10px 28px rgba(110, 149, 240, 0.10);
    padding: 28px 28px 24px;
}

.transaction-create-form {
    width: 100% !important;
    max-width: 100%;
    margin: 0 auto !important;
    text-align: left;
    font-size: 1rem;
}

.transaction-create-form select,
.transaction-create-form input:not([type="submit"]) {
    width: 100% !important;
    max-width: 100%;
    display: block;
    margin: 0 0 12px 0 !important;
    height: 48px;
    padding: 0 14px;
    border: 1px solid #c9d6ff;
    border-radius: 10px;
    background: #fff;
    box-sizing: border-box;
    font-size: 1rem;
    font-family: Calibri, sans-serif;
}

.transaction-create-form select:invalid {
    color: #6b7280;
}

.transaction-create-form input[type="date"] {
    appearance: auto;
}

.transaction-create-cc-field {
    margin-bottom: 12px;
}

.transaction-create-cc-field select {
    margin-bottom: 0 !important;
}

.transaction-create-submit {
    display: block !important;
    width: 220px !important;
    max-width: 100%;
    height: 48px;
    margin: 18px auto 0 !important;
    border-radius: 10px;
    background-color: #6e95f0;
    color: #fff;
    border: 2px solid #000;
    cursor: pointer;
    font-family: Calibri, sans-serif;
    font-size: 1rem;
    font-weight: 600;
    text-align: center;
}

.transaction-create-submit:hover {
    background-color: #fff;
    color: #6e95f0;
}

@media (max-width: 768px) {
    .transaction-create-page {
        padding: 20px 14px 40px;
    }

    .transaction-create-card {
        padding: 20px 16px;
        border-radius: 12px;
    }

    .transaction-create-submit {
        width: 100% !important;
    }
}
</style>

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