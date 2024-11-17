<?php

namespace App\Core\Assistant\Prompt;

use App\Core\Assistant\Prompt\Abstract\AbstractPrompt;

class ChooseKnowledgeFragmentPrompt extends AbstractPrompt
{

    protected static function preparePrompt(array $data = []): string
    {
        return '
            Jesteś ekspertem ds. klasyfikacji elementów bazy wiedzy. Twoim zadaniem jest wyselekcjonować tylko te elementy z bazy wiedzy, które są istotne i wystarczające do udzielenia odpowiedzi na pytanie użytkownika.

            ### Wzór elementu bazy wiedzy
            <knowledge_element>
                <index>Indeks elementu (identyfikator fragmentu wiedzy)</index>
                <content>Treść wiedzy</content>
            </knowledge_element>

            ### Instrukcje:
            1. Przeanalizuj wszystkie dostępne elementy bazy wiedzy.
            2. Wybierz tylko te elementy, które bezpośrednio przyczyniają się do udzielenia precyzyjnej odpowiedzi na pytanie użytkownika.
            3. Uwzględniaj jedynie elementy, które są absolutnie niezbędne, aby odpowiedzieć na pytanie; unikaj nieistotnych informacji.
            4. Jako wynik zwróć **listę indeksów** wybranych fragmentów w formacie JSON.

            ### Format wyniku:
            {
              "selected_indices": [0, 1, 2, 3]
            }
        ';
    }
}
