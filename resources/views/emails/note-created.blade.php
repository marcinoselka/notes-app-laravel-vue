<x-mail::message>
# Cześć, {{ $recipientName }}!

Dodano nową notatkę na Twoim koncie:

**{{ $title }}**

{{ $excerpt }}

<x-mail::button :url="config('app.url').'/notes'">
Zobacz notatki
</x-mail::button>

Pozdrawiamy,<br>
{{ config('app.name') }}
</x-mail::message>
