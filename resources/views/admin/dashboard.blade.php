@extends('layouts.app')

@section('title', 'Admin Dashboard - ClockWise')

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 py-6 sm:py-10 space-y-6 sm:space-y-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold">Admin Dashboard</h1>
            <p class="text-sm text-slate-500 break-all">Office IP: {{ $office_ip ?? 'Not set' }}</p>
        </div>
        <form action="{{ route('admin.set-ip') }}" method="POST">
            @csrf
            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white hover:bg-slate-800 w-full sm:w-auto">
                🔄 Set Office IP to Current
            </button>
        </form>
    </div>

    <section class="grid gap-4 grid-cols-2 sm:grid-cols-3 md:grid-cols-5">
        <div class="rounded-lg bg-white p-4 shadow-sm">
            <p class="text-xs uppercase text-slate-500">Office logs</p>
            <p class="text-2xl font-semibold">{{ $office_count }}</p>
        </div>
        <div class="rounded-lg bg-white p-4 shadow-sm">
            <p class="text-xs uppercase text-slate-500">Remote logs</p>
            <p class="text-2xl font-semibold">{{ $remote_count }}</p>
        </div>
        <div class="rounded-lg bg-white p-4 shadow-sm">
            <p class="text-xs uppercase text-slate-500">On time</p>
            <p class="text-2xl font-semibold">{{ $ontime_count }}</p>
        </div>
        <div class="rounded-lg bg-white p-4 shadow-sm">
            <p class="text-xs uppercase text-slate-500">Late</p>
            <p class="text-2xl font-semibold">{{ $late_count }}</p>
        </div>
        <div class="rounded-lg bg-white p-4 shadow-sm">
            <p class="text-xs uppercase text-slate-500">MC</p>
            <p class="text-2xl font-semibold">{{ $mc_count }}</p>
        </div>
    </section>

    <section class="rounded-lg bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold">Employees</h2>
            <span class="text-xs text-slate-500">Total: {{ count($employees) }}</span>
        </div>
        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b text-xs uppercase text-slate-500">
                        <th class="py-2">Name</th>
                        <th class="py-2">Email</th>
                        <th class="py-2">Position</th>
                        <th class="py-2">Total hours</th>
                        <th class="py-2">Last status</th>
                        <th class="py-2">Last date</th>
                        <th class="py-2">Last location</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($employees as $employee)
                        <tr class="border-b">
                            <td class="py-2 font-medium">{{ $employee['name'] }}</td>
                            <td class="py-2 text-slate-500">{{ $employee['email'] }}</td>
                            <td class="py-2">{{ $employee['position'] ?? '-' }}</td>
                            <td class="py-2">{{ $employee['total_hours'] }}</td>
                            <td class="py-2 capitalize">{{ $employee['last_status'] }}</td>
                            <td class="py-2">{{ $employee['last_date'] ?? '-' }}</td>
                            <td class="py-2 capitalize">{{ $employee['last_location'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-slate-500">No employees found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="rounded-lg bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold">Attendance Log (Recent)</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b text-xs uppercase text-slate-500">
                        <th class="py-2">Employee</th>
                        <th class="py-2">Date</th>
                        <th class="py-2">Clock in</th>
                        <th class="py-2">Clock out</th>
                        <th class="py-2">Status</th>
                        <th class="py-2">Location</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr class="border-b">
                            <td class="py-2 font-medium">{{ $log->user?->name ?? 'Unknown' }}</td>
                            <td class="py-2">{{ $log->date?->toDateString() }}</td>
                            <td class="py-2">{{ $log->clock_in ? \Carbon\Carbon::parse($log->clock_in)->format('H:i') : '-' }}</td>
                            <td class="py-2">{{ $log->clock_out ? \Carbon\Carbon::parse($log->clock_out)->format('H:i') : '-' }}</td>
                            <td class="py-2 capitalize">{{ $log->status }}</td>
                            <td class="py-2 capitalize">{{ $log->location_type }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-500">No attendance logs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
