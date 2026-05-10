<x-admin-layout heading="Edit shop item" eyebrow="{{ $item->name }}">
    @include('admin.shop._form', ['item' => $item])
</x-admin-layout>
