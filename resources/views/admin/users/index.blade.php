<x-admin-layout heading="Users" eyebrow="Account management">
    <div class="mb-6 flex flex-wrap gap-2">
        <a href="{{ route('admin.users.index', ['filter' => 'all', 'q' => request('q')]) }}"
        class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold transition
        {{ $filter === 'all' ? 'bg-prism-violet text-white' : 'border border-ink-200 text-ink-700 hover:border-prism-violet hover:text-prism-violet' }}">
            All Users
            <span class="rounded-full bg-white/20 px-2 py-0.5 text-xs font-bold">
                {{ $statusCounts['all'] }}
            </span>
        </a>
        <a href="{{ route('admin.users.index', ['filter' => 'customer', 'q' => request('q')]) }}"
        class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold transition
        {{ $filter === 'customer' ? 'bg-blue-100 text-blue-700' : 'border border-ink-200 text-ink-700 hover:border-blue-300 hover:text-blue-600' }}">
            👤 Customer
            <span class="rounded-full {{ $filter === 'customer' ? 'bg-blue-200' : 'bg-blue-50' }} px-2 py-0.5 text-xs font-bold">
                {{ $statusCounts['customer'] }}
            </span>
        </a>
        <a href="{{ route('admin.users.index', ['filter' => 'admin', 'q' => request('q')]) }}"
        class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold transition
        {{ $filter === 'admin' ? 'bg-ink-200 text-ink-800' : 'border border-ink-200 text-ink-700 hover:border-ink-300' }}">
            🛡️ Admin
            <span class="rounded-full {{ $filter === 'admin' ? 'bg-ink-300' : 'bg-ink-100' }} px-2 py-0.5 text-xs font-bold">
                {{ $statusCounts['admin'] }}
            </span>
        </a>
    </div>

    <form method="GET" class="mb-5 flex gap-2">
        <input type="hidden" name="filter" value="{{ request('filter', 'all') }}">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search name or email…"
            class="flex-1 rounded-full border-ink-200 text-sm">
        <button type="submit" class="rounded-full bg-ink-900 px-5 py-2 text-sm font-bold text-white">
            Search
        </button>
    </form>

    <div class="overflow-hidden rounded-3xl border border-ink-200 bg-white">
        <div class="overflow-x-auto [-webkit-overflow-scrolling:touch]">
            <table class="min-w-full text-sm">
                <thead class="bg-ink-50 text-left text-[10px] font-bold uppercase tracking-widest text-ink-500">
                    <tr>
                        <th class="px-4 py-3">User</th>
                        <th class="px-4 py-3">Role</th>
                        <th class="px-4 py-3">Verified</th>
                        <th class="px-4 py-3">Joined</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100">
                    @foreach ($users as $u)
                        <tr class="hover:bg-ink-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    @if ($u->avatar)
                                        <img src="{{ asset('storage/' . $u->avatar) }}"
                                            alt="Avatar {{ $u->name }}"
                                            class="h-8 w-8 rounded-full object-cover">
                                    @else
                                        <span
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-full prism-bg text-xs font-bold text-white">
                                            {{ Str::upper(Str::substr($u->name, 0, 1)) }}
                                        </span>
                                    @endif
                                    <div>
                                        <p class="font-bold">{{ $u->name }}</p>
                                        <p class="text-[10px] text-ink-500">{{ $u->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="rounded-full {{ $u->isAdmin() ? 'prism-bg text-white' : 'bg-ink-100 text-ink-700' }} px-2 py-0.5 text-[10px] font-bold uppercase tracking-widest">{{ $u->role }}</span>
                            </td>
                            <td class="px-4 py-3 text-xs">{{ $u->email_verified_at ? '✓ Yes' : '— No' }}</td>
                            <td class="px-4 py-3 text-xs text-ink-500 whitespace-nowrap">
                                {{ $u->created_at->diffForHumans() }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.users.show', $u) }}"
                                    class="text-xs font-semibold hover:text-prism-violet">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $users->links() }}</div>

</x-admin-layout>
