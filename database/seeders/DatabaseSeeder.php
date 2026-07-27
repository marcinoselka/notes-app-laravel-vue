<?php

namespace Database\Seeders;

use App\Models\Note;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $testUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Zestaw realistycznych notatek (zamiast losowego Fakera) — pozwala
        // zademonstrować paginację API/UI z sensowną, czytelną treścią.
        collect($this->demoNotes())->each(function (array $note, int $index) use ($testUser) {
            Note::factory()->for($testUser)->create([
                'title' => $note[0],
                'content' => $note[1],
                'is_pinned' => in_array($index, [1, 8, 18, 28, 38], true),
            ]);
        });

        Notification::factory(3)->for($testUser)->create();
        Notification::factory(2)->read()->for($testUser)->create();

        $otherUsers = User::factory(3)->create();
        $otherUsers->each(function (User $user) {
            Note::factory(5)->for($user)->create();
        });
    }

    /**
     * @return array<int, array{0: string, 1: string}>
     */
    private function demoNotes(): array
    {
        return [
            ['Zakupy na weekend', 'Chleb, mleko, jajka, masło, warzywa na zupę, owoce dla dzieci.'],
            ['Spotkanie z zespołem - poniedziałek', 'Omówić postęp sprintu, retro z poprzedniego tygodnia, priorytety na ten tydzień.'],
            ['Pomysł na prezent dla mamy', 'Kwiaty + bilety do teatru na przedstawienie w marcu.'],
            ['Lista rzeczy do spakowania na wyjazd', 'Ładowarka, paszport, adapter do gniazdek, słuchawki, książka.'],
            ['Notatki ze szkolenia Laravel', 'Sanctum do autoryzacji SPA, Eloquent relacje, kolejki i eventy.'],
            ['Plan renowacji balkonu', 'Zamówić panele podłogowe, kupić farbę do metalu, umówić ekipę.'],
            ['Przepis na sernik babci', 'Ser, jajka, cukier, wanilia, budyń śmietankowy, spód herbatnikowy.'],
            ['TODO przed wdrożeniem', 'Sprawdzić migracje, backup bazy, poinformować zespół, monitoring.'],
            ['Cele na Q3', 'Zwiększyć pokrycie testami, wdrożyć CI/CD, poprawić czas ładowania strony.'],
            ['Numer telefonu hydraulika', 'Pan Tomasz, poleca sąsiadka, kontakt w razie awarii.'],
            ['Do przeczytania', 'Clean Architecture, Refactoring Fowlera, Domain-Driven Design.'],
            ['Plan urlopu w sierpniu', 'Zarezerwować nocleg nad morzem, sprawdzić pogodę, spakować rowery.'],
            ['Pomysły na bloga', 'Porównanie Vue vs React, wzorzec Repository w Laravel, testowanie API.'],
            ['Hasła do zmiany', 'Serwer produkcyjny, panel admina, konto pocztowe firmowe.'],
            ['Lista prezentów świątecznych', 'Tata - zestaw narzędzi, siostra - perfumy, babcia - koc.'],
            ['Notatka z rozmowy z klientem', 'Chce dodać eksport do PDF i powiadomienia mailowe.'],
            ['Plan treningowy', 'Poniedziałek nogi, środa plecy, piątek cardio.'],
            ['Rzeczy do naprawy w domu', 'Cieknący kran w łazience, skrzypiące drzwi, żarówka w piwnicy.'],
            ['Checklist przed publikacją API', 'Dokumentacja endpointów, wersjonowanie, rate limiting.'],
            ['Pomysł na aplikację', 'Tracker nawyków z gamifikacją i przypomnieniami push.'],
            ['Lista zakupów świątecznych', 'Karp, kapusta, mak, orzechy, świece na choinkę.'],
            ['Notatki z code review', 'Zwrócić uwagę na zapytania N+1, brak walidacji w kontrolerze.'],
            ['Plan podróży do Krakowa', 'Wawel, Kazimierz, kolacja w Starym Mieście, powrót w niedzielę.'],
            ['Ważne kontakty awaryjne', 'Elektryk, ślusarz, weterynarz, przychodnia całodobowa.'],
            ['Pomysły na usprawnienie onboardingu', 'Skrócić formularz rejestracji, dodać tooltipy, wideo powitalne.'],
            ['Do zrobienia przed urlopem', 'Ustawić autoresponder, przekazać zadania, poprosić sąsiada o podlanie kwiatków.'],
            ['Lista subskrypcji do przejrzenia', 'Netflix, Spotify, siłownia - sprawdzić czy używane.'],
            ['Notatka - błąd w produkcji', 'Timeout przy eksporcie dużych plików CSV, zgłoszone do zespołu backend.'],
            ['Plan na spotkanie 1:1', 'Omówić rozwój zawodowy, feedback od zespołu, cele na kwartał.'],
            ['Inspiracje do wnętrza salonu', 'Kolor ścian - szałwiowy, drewniana komoda, duże lustro.'],
            ['Lista książek do kupienia', 'Atomic Habits, Sapiens, Projekt Feniks.'],
            ['Notatka z wdrożenia', 'Wdrożenie przebiegło bez przestojów, monitoring zielony przez 24h.'],
            ['Plan posiłków na tydzień', 'Poniedziałek makaron, wtorek zupa, środa ryba, czwartek kurczak.'],
            ['Do ogarnięcia w garażu', 'Posegregować narzędzia, oddać stare opony, kupić półki.'],
            ['Pomysł na prezent urodzinowy dla przyjaciela', 'Bilet na koncert + płyta winylowa jego ulubionego zespołu.'],
            ['Notatki z konferencji Laravel Live', 'Nowości w Laravel 13, Livewire 4, najlepsze praktyki testowania.'],
            ['Lista rzeczy do sprawdzenia przed audytem bezpieczeństwa', 'Nagłówki HTTP, rate limiting, szyfrowanie danych wrażliwych.'],
            ['Plan na weekend z dziećmi', 'Zoo w sobotę, kino w niedzielę, lody po obiedzie.'],
            ['Pomysły na usprawnienie kolejki zadań', 'Dodać priorytety, dashboard z metrykami, alerty przy failed jobs.'],
            ['Notatka - refaktoryzacja modułu płatności', 'Wydzielić PaymentService, dodać testy, poprawić obsługę błędów Stripe.'],
        ];
    }
}
