<?php

namespace Core\Controllers;

use Core\Repositories\CreditCardRepository;

class CreditCardsController extends Controller
{
    public function __construct(
        private CreditCardRepository $creditCardRepository
    ) {
    }

    public function index(): void
    {
        $this->requireMethod('GET');

        $creditCards = $this->creditCardRepository->all();

        view('creditcards/index.view.php', [
            'title' => 'Credit Cards',
            'heading' => 'Credit Cards',
            'creditCards' => $creditCards,
        ]);
    }
}