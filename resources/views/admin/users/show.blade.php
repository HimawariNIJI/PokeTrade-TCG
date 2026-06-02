<x-admin-layout heading="{{ $user->name }}" eyebrow="User detail">


@if ($errors->any())
    <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 mb-6">
        <ul class="list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div class="grid gap-6 lg:grid-cols-12">
    <div class="lg:col-span-4 rounded-3xl border border-ink-200 bg-white p-6">
        <div class="flex items-center gap-3">
            <span class="inline-flex h-14 w-14 items-center justify-center rounded-full prism-bg text-xl font-bold text-white">
                {{ Str::upper(Str::substr($user->name, 0, 1)) }}
            </span>
            <div>
                <p class="font-display text-lg font-black">{{ $user->name }}</p>
                <p class="text-xs text-ink-500">{{ $user->email }}</p>
            </div>
        </div>
        <dl class="mt-6 space-y-2 text-sm">
            <div class="flex justify-between"><dt class="text-ink-500">Role</dt><dd>{{ $user->role }}</dd></div>
            <div class="flex justify-between"><dt class="text-ink-500">Joined</dt><dd>{{ $user->created_at->format('M j, Y') }}</dd></div>
            <div class="flex justify-between"><dt class="text-ink-500">Orders</dt><dd>{{ $user->orders->count() }}</dd></div>
            <div class="flex justify-between"><dt class="text-ink-500">Spent</dt><dd class="font-mono">@idr($user->orders->where('payment_status', 'paid')->sum('total'))</dd></div>
        </dl>

        <form method="POST" action="{{ route('admin.users.updateRole', $user) }}" class="mt-6">
            @csrf @method('PATCH')
            <select name="role" class="w-full rounded-xl border-ink-200 text-sm">
                <option value="customer" @selected($user->role === 'customer')>Customer</option>
                <option value="admin" @selected($user->role === 'admin')>Admin</option>
            </select>
            <x-prism-button type="submit" size="sm" class="mt-2 w-full">Update role</x-prism-button>
        </form>
    </div>

    <div class="lg:col-span-8 rounded-3xl border border-ink-200 bg-white p-6">
        <h2 class="font-display text-base font-black">Recent orders</h2>
        @if($user->orders->isEmpty())
            <p class="mt-3 text-sm text-ink-500">No orders.</p>
        @else
            <ul class="mt-3 divide-y divide-ink-100">
                @foreach($user->orders as $o)
                    <li class="flex items-center justify-between py-3 text-sm">
                        <a href="{{ route('admin.orders.show', $o) }}" class="font-mono text-xs hover:text-prism-violet">{{ $o->code }}</a>
                        <span class="rounded-full bg-{{ $o->status_color }}-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-widest text-{{ $o->status_color }}-700">{{ $o->status }}</span>
                        <span class="font-mono">@idr($o->total)</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>

</x-admin-layout>
