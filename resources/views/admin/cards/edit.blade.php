<x-admin-layout heading="Edit card" eyebrow="{{ $card->name }}">
    @include('admin.cards._form', ['card' => $card])
</x-admin-layout>
