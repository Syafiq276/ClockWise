@extends('layouts.app')

@section('title', 'Reports & Analytics')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<style>
    .preset-btn { @apply px-3 py-1.5 text-xs font-medium rounded-lg border transition-all duration-200; }
    .preset-btn.active { @apply bg-blue-600 text-white border-blue-600 shadow-md; }
    .preset-btn:not(.active) { @apply bg-white text-gray-600 border-gray-300 hover:bg-gray-50; }
    .change-badge { @apply inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full; }
    .change-up { @apply bg-green-100 text-green-700; }
    .change-down { @apply bg-red-100 text-red-700; }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8" x-data="{ tab: 'attendance', exportOpen: false }">
    {{-- ── Header ────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">📊 Reports & Analytics</h1>
            <p class="text-gray-500 text-sm">Period: {{ $startDate->format('d M Y') }} — {{ $endDate->format('d M Y') }}</p>
        </div>
        {{-- Export dropdown --}}
        <div class="relative">
            <button @click="exportOpen = !exportOpen" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition shadow">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export CSV
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="exportOpen" @click.away="exportOpen = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border z-50">
                <a href="{{ route('admin.reports.export', array_merge(request()->query(), ['type' => 'attendance'])) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-t-lg">📋 Attendance Data</a>
                <a href="{{ route('admin.reports.export', array_merge(request()->query(), ['type' => 'payroll'])) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">💰 Payroll Data</a>
                <a href="{{ route('admin.reports.export', array_merge(request()->query(), ['type' => 'performance'])) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-b-lg">🏆 Performance Data</a>
            </div>
        </div>
    </div>

    {{-- ── Period Presets ─────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-md p-4 mb-6">
        <div class="flex flex-wrap gap-2 mb-4">
            @php
                $presets = [
                    'this_week' => 'This Week', 'last_week' => 'Last Week',
                    'this_month' => 'This Month', 'last_month' => 'Last Month',
                    'this_quarter' => 'This Quarter', 'last_quarter' => 'Last Quarter',
                    'q1' => 'Q1', 'q2' => 'Q2', 'q3' => 'Q3', 'q4' => 'Q4',
                    'this_year' => 'This Year', 'last_year' => 'Last Year',
                ];
            @endphp
            @foreach($presets as $key => $label)
                <a href="{{ route('admin.reports', ['preset' => $key]) }}"
                   class="preset-btn {{ $preset === $key ? 'active' : '' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
        <form method="GET" action="{{ route('admin.reports') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Custom Start</label>
                <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}"
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Custom End</label>
                <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}"
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition">Apply</button>
            <a href="{{ route('admin.reports') }}" class="px-4 py-2 border border-gray-300 text-gray-600 text-sm rounded-lg hover:bg-gray-50 transition">Reset</a>
        </form>
    </div>

    {{-- ── Summary Cards with Comparison ──────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        {{-- Total Records --}}
        <div class="bg-white rounded-xl shadow-md p-4">
            <div class="flex items-center justify-between mb-2">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                @if($stats['attendance_change'] != 0)
                    <span class="change-badge {{ $stats['attendance_change'] > 0 ? 'change-up' : 'change-down' }}">
                        {{ $stats['attendance_change'] > 0 ? '↑' : '↓' }} {{ abs($stats['attendance_change']) }}%
                    </span>
                @endif
            </div>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['total_attendance']) }}</p>
            <p class="text-xs text-gray-500">Total Records</p>
        </div>

        {{-- Active Employees --}}
        <div class="bg-white rounded-xl shadow-md p-4">
            <div class="flex items-center justify-between mb-2">
                <div class="p-2 bg-green-100 rounded-lg">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-green-600">{{ $stats['unique_employees'] }}</p>
            <p class="text-xs text-gray-500">Active Employees</p>
        </div>

        {{-- Punctuality Rate --}}
        <div class="bg-white rounded-xl shadow-md p-4">
            <div class="flex items-center justify-between mb-2">
                <div class="p-2 bg-emerald-100 rounded-lg">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                @php $punctDiff = $stats['punctuality_rate'] - $stats['prev_punctuality']; @endphp
                @if($punctDiff != 0)
                    <span class="change-badge {{ $punctDiff > 0 ? 'change-up' : 'change-down' }}">
                        {{ $punctDiff > 0 ? '↑' : '↓' }} {{ abs(round($punctDiff, 1)) }}%
                    </span>
                @endif
            </div>
            <p class="text-2xl font-bold text-emerald-600">{{ $stats['punctuality_rate'] }}%</p>
            <p class="text-xs text-gray-500">Punctuality Rate</p>
        </div>

        {{-- Avg Hours --}}
        <div class="bg-white rounded-xl shadow-md p-4">
            <div class="flex items-center justify-between mb-2">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                @if($stats['avg_hours_change'] != 0)
                    <span class="change-badge {{ $stats['avg_hours_change'] > 0 ? 'change-up' : 'change-down' }}">
                        {{ $stats['avg_hours_change'] > 0 ? '+' : '' }}{{ $stats['avg_hours_change'] }}h
                    </span>
                @endif
            </div>
            <p class="text-2xl font-bold text-purple-600">{{ $stats['avg_hours'] }}h</p>
            <p class="text-xs text-gray-500">Avg Hours/Day</p>
        </div>

        {{-- Total Payroll --}}
        <div class="bg-white rounded-xl shadow-md p-4">
            <div class="flex items-center justify-between mb-2">
                <div class="p-2 bg-amber-100 rounded-lg">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                @if($stats['payroll_change'] != 0)
                    <span class="change-badge {{ $stats['payroll_change'] > 0 ? 'change-up' : 'change-down' }}">
                        {{ $stats['payroll_change'] > 0 ? '↑' : '↓' }} {{ abs($stats['payroll_change']) }}%
                    </span>
                @endif
            </div>
            <p class="text-2xl font-bold text-amber-600">RM {{ number_format($stats['total_payroll'], 0) }}</p>
            <p class="text-xs text-gray-500">Total Payroll</p>
        </div>
    </div>

    {{-- ── Tabs ───────────────────────────────────────────────── --}}
    <div class="mb-6">
        <div class="flex gap-1 border-b border-gray-200 mb-0 overflow-x-auto">
            <button @click="tab = 'attendance'" :class="tab === 'attendance' ? 'text-blue-600 border-blue-600 bg-white' : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300'"
                    class="px-4 py-2.5 text-sm font-medium rounded-t-lg border-b-2 transition-all duration-200 cursor-pointer whitespace-nowrap">📋 Attendance</button>
            <button @click="tab = 'payroll'" :class="tab === 'payroll' ? 'text-blue-600 border-blue-600 bg-white' : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300'"
                    class="px-4 py-2.5 text-sm font-medium rounded-t-lg border-b-2 transition-all duration-200 cursor-pointer whitespace-nowrap">💰 Payroll Costs</button>
            <button @click="tab = 'performance'" :class="tab === 'performance' ? 'text-blue-600 border-blue-600 bg-white' : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300'"
                    class="px-4 py-2.5 text-sm font-medium rounded-t-lg border-b-2 transition-all duration-200 cursor-pointer whitespace-nowrap">🏆 Performance</button>
            <button @click="tab = 'overtime'" :class="tab === 'overtime' ? 'text-blue-600 border-blue-600 bg-white' : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300'"
                    class="px-4 py-2.5 text-sm font-medium rounded-t-lg border-b-2 transition-all duration-200 cursor-pointer whitespace-nowrap">⏰ Overtime</button>
            <button @click="tab = 'leave'" :class="tab === 'leave' ? 'text-blue-600 border-blue-600 bg-white' : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300'"
                    class="px-4 py-2.5 text-sm font-medium rounded-t-lg border-b-2 transition-all duration-200 cursor-pointer whitespace-nowrap">🌴 Leave</button>
        </div>

        {{-- ═══════════════════════════════════════════════════════
             TAB 1: ATTENDANCE
             ═══════════════════════════════════════════════════════ --}}
        <div x-show="tab === 'attendance'" x-transition class="space-y-6 pt-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Daily Attendance</h3>
                    <div class="h-72"><canvas id="dailyAttendanceChart"></canvas></div>
                </div>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Weekly Trend (Last 4 Weeks)</h3>
                    <div class="h-72"><canvas id="weeklyTrendChart"></canvas></div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Work Location</h3>
                    <div class="h-64 flex items-center justify-center"><canvas id="locationChart"></canvas></div>
                </div>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Leave Types</h3>
                    <div class="h-64 flex items-center justify-center"><canvas id="leaveChart"></canvas></div>
                </div>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Top Employees (Hours)</h3>
                    <div class="h-64"><canvas id="employeeHoursChart"></canvas></div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════
             TAB 2: PAYROLL COSTS
             ═══════════════════════════════════════════════════════ --}}
        <div x-show="tab === 'payroll'" x-transition class="space-y-6 pt-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Payroll Cost Trend</h3>
                    <div class="h-80"><canvas id="payrollTrendChart"></canvas></div>
                </div>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Statutory Deductions Breakdown</h3>
                    <div class="h-80"><canvas id="statutoryChart"></canvas></div>
                </div>
            </div>

            @if($payrollTrend->count())
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Monthly Payroll Summary</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Month</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Gross Pay</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Statutory</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Overtime</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Net Pay</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($payrollTrend as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 font-medium text-gray-800">{{ \Carbon\Carbon::parse($row->month . '-01')->format('M Y') }}</td>
                                <td class="px-6 py-3 text-right text-gray-600">RM {{ number_format($row->gross, 2) }}</td>
                                <td class="px-6 py-3 text-right text-red-600">RM {{ number_format($row->statutory, 2) }}</td>
                                <td class="px-6 py-3 text-right text-amber-600">RM {{ number_format($row->overtime, 2) }}</td>
                                <td class="px-6 py-3 text-right font-semibold text-green-600">RM {{ number_format($row->net, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 font-semibold">
                            <tr>
                                <td class="px-6 py-3 text-gray-800">Total</td>
                                <td class="px-6 py-3 text-right text-gray-800">RM {{ number_format($payrollTrend->sum('gross'), 2) }}</td>
                                <td class="px-6 py-3 text-right text-red-600">RM {{ number_format($payrollTrend->sum('statutory'), 2) }}</td>
                                <td class="px-6 py-3 text-right text-amber-600">RM {{ number_format($payrollTrend->sum('overtime'), 2) }}</td>
                                <td class="px-6 py-3 text-right text-green-600">RM {{ number_format($payrollTrend->sum('net'), 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            @else
            <div class="bg-white rounded-xl shadow-md p-12 text-center">
                <p class="text-gray-400 text-lg">No payroll data for this period</p>
            </div>
            @endif
        </div>

        {{-- ═══════════════════════════════════════════════════════
             TAB 3: PERFORMANCE SCORECARDS
             ═══════════════════════════════════════════════════════ --}}
        <div x-show="tab === 'performance'" x-transition class="space-y-6 pt-6">
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800">Employee Performance Scorecards</h3>
                    <a href="{{ route('admin.reports.export', array_merge(request()->query(), ['type' => 'performance'])) }}"
                       class="text-sm text-blue-600 hover:text-blue-800 font-medium">Export CSV →</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Position</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Attendance</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Punctuality</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Hours</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Overtime</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Late</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Leave</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($performance as $emp)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $emp['name'] }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $emp['position'] }}</td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <div class="w-16 bg-gray-200 rounded-full h-2">
                                            <div class="h-2 rounded-full {{ $emp['attendance_rate'] >= 90 ? 'bg-green-500' : ($emp['attendance_rate'] >= 75 ? 'bg-yellow-500' : 'bg-red-500') }}"
                                                 style="width: {{ min($emp['attendance_rate'], 100) }}%"></div>
                                        </div>
                                        <span class="text-xs font-semibold {{ $emp['attendance_rate'] >= 90 ? 'text-green-600' : ($emp['attendance_rate'] >= 75 ? 'text-yellow-600' : 'text-red-600') }}">
                                            {{ $emp['attendance_rate'] }}%
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <div class="w-16 bg-gray-200 rounded-full h-2">
                                            <div class="h-2 rounded-full {{ $emp['punctuality_rate'] >= 90 ? 'bg-emerald-500' : ($emp['punctuality_rate'] >= 75 ? 'bg-yellow-500' : 'bg-red-500') }}"
                                                 style="width: {{ min($emp['punctuality_rate'], 100) }}%"></div>
                                        </div>
                                        <span class="text-xs font-semibold {{ $emp['punctuality_rate'] >= 90 ? 'text-emerald-600' : ($emp['punctuality_rate'] >= 75 ? 'text-yellow-600' : 'text-red-600') }}">
                                            {{ $emp['punctuality_rate'] }}%
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right text-gray-600">{{ $emp['total_hours'] }}h</td>
                                <td class="px-4 py-3 text-right {{ $emp['overtime_hours'] > 0 ? 'text-amber-600 font-semibold' : 'text-gray-400' }}">{{ $emp['overtime_hours'] }}h</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $emp['late_days'] > 3 ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $emp['late_days'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center text-gray-600">{{ $emp['leave_days'] }}d</td>
                            </tr>
                            @empty
                            <tr><td colspan="8" class="px-4 py-12 text-center text-gray-400">No employee data for this period</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════
             TAB 4: OVERTIME ANALYSIS
             ═══════════════════════════════════════════════════════ --}}
        <div x-show="tab === 'overtime'" x-transition class="space-y-6 pt-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-2">
                @php
                    $totalOtHours = $overtimeTrend->sum('ot_hours');
                    $totalOtDays  = $overtimeTrend->sum('ot_count');
                    $avgOtDaily   = $overtimeTrend->count() > 0 ? round($totalOtHours / $overtimeTrend->count(), 1) : 0;
                @endphp
                <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl p-5 border border-amber-200">
                    <p class="text-xs font-medium text-amber-600 uppercase mb-1">Total OT Hours</p>
                    <p class="text-3xl font-bold text-amber-700">{{ $totalOtHours }}h</p>
                </div>
                <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl p-5 border border-orange-200">
                    <p class="text-xs font-medium text-orange-600 uppercase mb-1">OT Occurrences</p>
                    <p class="text-3xl font-bold text-orange-700">{{ $totalOtDays }}</p>
                </div>
                <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-5 border border-red-200">
                    <p class="text-xs font-medium text-red-600 uppercase mb-1">Avg OT / Day</p>
                    <p class="text-3xl font-bold text-red-700">{{ $avgOtDaily }}h</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Daily Overtime Trend</h3>
                <div class="h-80"><canvas id="overtimeChart"></canvas></div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════
             TAB 5: LEAVE UTILISATION
             ═══════════════════════════════════════════════════════ --}}
        <div x-show="tab === 'leave'" x-transition class="space-y-6 pt-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Monthly Leave Utilisation</h3>
                    <div class="h-80"><canvas id="leaveTrendChart"></canvas></div>
                </div>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Leave Distribution</h3>
                    <div class="h-80 flex items-center justify-center"><canvas id="leaveDistChart"></canvas></div>
                </div>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                @php
                    $totalLeaveDays = $leaveTrend->sum('total_days');
                    $annualDays     = $leaveTrend->sum('annual');
                    $medicalDays    = $leaveTrend->sum('medical');
                    $emergencyDays  = $leaveTrend->sum('emergency');
                @endphp
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
                    <p class="text-xs font-medium text-blue-600 uppercase">Total Leave Days</p>
                    <p class="text-2xl font-bold text-blue-700 mt-1">{{ $totalLeaveDays }}</p>
                </div>
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-4 border border-green-200">
                    <p class="text-xs font-medium text-green-600 uppercase">Annual Leave</p>
                    <p class="text-2xl font-bold text-green-700 mt-1">{{ $annualDays }}</p>
                </div>
                <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-4 border border-red-200">
                    <p class="text-xs font-medium text-red-600 uppercase">Medical Leave</p>
                    <p class="text-2xl font-bold text-red-700 mt-1">{{ $medicalDays }}</p>
                </div>
                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl p-4 border border-yellow-200">
                    <p class="text-xs font-medium text-yellow-600 uppercase">Emergency Leave</p>
                    <p class="text-2xl font-bold text-yellow-700 mt-1">{{ $emergencyDays }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Back Link --}}
    <div class="mt-8">
        <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:text-blue-800 inline-flex items-center text-sm">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Dashboard
        </a>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     CHART.JS SCRIPTS
     ═══════════════════════════════════════════════════════════ --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    Chart.defaults.font.family = 'system-ui, -apple-system, sans-serif';
    Chart.defaults.font.size = 12;
    Chart.defaults.plugins.legend.display = true;

    const palette = {
        blue:   { bg: 'rgba(59, 130, 246, 0.15)', border: 'rgb(59, 130, 246)' },
        green:  { bg: 'rgba(34, 197, 94, 0.15)',  border: 'rgb(34, 197, 94)' },
        amber:  { bg: 'rgba(245, 158, 11, 0.15)', border: 'rgb(245, 158, 11)' },
        red:    { bg: 'rgba(239, 68, 68, 0.15)',   border: 'rgb(239, 68, 68)' },
        purple: { bg: 'rgba(168, 85, 247, 0.15)',  border: 'rgb(168, 85, 247)' },
    };

    // ── 1. Daily Attendance (Line) ─────────────────────────
    new Chart(document.getElementById('dailyAttendanceChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($dailyAttendance->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))) !!},
            datasets: [{
                label: 'On Time',
                data: {!! json_encode($dailyAttendance->pluck('ontime')) !!},
                borderColor: palette.green.border, backgroundColor: palette.green.bg,
                fill: true, tension: 0.3, pointRadius: 3
            }, {
                label: 'Late',
                data: {!! json_encode($dailyAttendance->pluck('late')) !!},
                borderColor: palette.amber.border, backgroundColor: palette.amber.bg,
                fill: true, tension: 0.3, pointRadius: 3
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });

    // ── 2. Weekly Trend (Bar) ──────────────────────────────
    new Chart(document.getElementById('weeklyTrendChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($weeklyTrend->pluck('week')) !!},
            datasets: [{
                label: 'Total', data: {!! json_encode($weeklyTrend->pluck('total')) !!},
                backgroundColor: 'rgba(59, 130, 246, 0.8)', borderRadius: 6
            }, {
                label: 'On Time', data: {!! json_encode($weeklyTrend->pluck('ontime')) !!},
                backgroundColor: 'rgba(34, 197, 94, 0.8)', borderRadius: 6
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });

    // ── 3. Location Doughnut ───────────────────────────────
    const locData = @json($locationData);
    new Chart(document.getElementById('locationChart'), {
        type: 'doughnut',
        data: {
            labels: ['Office', 'Remote'],
            datasets: [{ data: [locData.office || 0, locData.remote || 0],
                backgroundColor: ['rgba(59,130,246,0.8)', 'rgba(168,85,247,0.8)'], borderWidth: 0 }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
    });

    // ── 4. Leave Types Doughnut ────────────────────────────
    const leaveData = @json($leaveData);
    const leaveLabels = { annual: 'Annual', mc: 'Medical', emergency: 'Emergency', unpaid: 'Unpaid' };
    new Chart(document.getElementById('leaveChart'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(leaveData).map(k => leaveLabels[k] || k),
            datasets: [{ data: Object.values(leaveData),
                backgroundColor: ['rgba(59,130,246,0.8)','rgba(239,68,68,0.8)','rgba(251,191,36,0.8)','rgba(107,114,128,0.8)'],
                borderWidth: 0 }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
    });

    // ── 5. Employee Hours (Horizontal Bar) ─────────────────
    const empData = @json($employeeHours);
    new Chart(document.getElementById('employeeHoursChart'), {
        type: 'bar',
        data: {
            labels: empData.map(e => e.name.split(' ')[0]),
            datasets: [{ label: 'Hours', data: empData.map(e => e.hours),
                backgroundColor: 'rgba(34,197,94,0.8)', borderRadius: 4 }]
        },
        options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true } } }
    });

    // ── 6. Payroll Cost Trend (Area) ───────────────────────
    const payrollData = @json($payrollTrend);
    if (document.getElementById('payrollTrendChart') && payrollData.length) {
        new Chart(document.getElementById('payrollTrendChart'), {
            type: 'line',
            data: {
                labels: payrollData.map(r => r.month),
                datasets: [{
                    label: 'Gross Pay', data: payrollData.map(r => parseFloat(r.gross)),
                    borderColor: palette.blue.border, backgroundColor: palette.blue.bg,
                    fill: true, tension: 0.3, pointRadius: 4
                }, {
                    label: 'Net Pay', data: payrollData.map(r => parseFloat(r.net)),
                    borderColor: palette.green.border, backgroundColor: palette.green.bg,
                    fill: true, tension: 0.3, pointRadius: 4
                }, {
                    label: 'Statutory', data: payrollData.map(r => parseFloat(r.statutory)),
                    borderColor: palette.red.border, backgroundColor: palette.red.bg,
                    fill: true, tension: 0.3, pointRadius: 4
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: { callbacks: { label: ctx => ctx.dataset.label + ': RM ' + ctx.parsed.y.toLocaleString() } }
                },
                scales: { y: { beginAtZero: true, ticks: { callback: v => 'RM ' + v.toLocaleString() } } }
            }
        });
    }

    // ── 7. Statutory Breakdown (Stacked Bar) ───────────────
    if (document.getElementById('statutoryChart') && payrollData.length) {
        new Chart(document.getElementById('statutoryChart'), {
            type: 'bar',
            data: {
                labels: payrollData.map(r => r.month),
                datasets: [{
                    label: 'Statutory', data: payrollData.map(r => parseFloat(r.statutory)),
                    backgroundColor: 'rgba(239,68,68,0.7)', borderRadius: 4
                }, {
                    label: 'Overtime Pay', data: payrollData.map(r => parseFloat(r.overtime)),
                    backgroundColor: 'rgba(245,158,11,0.7)', borderRadius: 4
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: { callbacks: { label: ctx => ctx.dataset.label + ': RM ' + ctx.parsed.y.toLocaleString() } }
                },
                scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true,
                    ticks: { callback: v => 'RM ' + v.toLocaleString() } } }
            }
        });
    }

    // ── 8. Overtime Trend (Combo: Bar + Line) ──────────────
    const otData = @json($overtimeTrend);
    if (document.getElementById('overtimeChart') && otData.length) {
        new Chart(document.getElementById('overtimeChart'), {
            type: 'bar',
            data: {
                labels: otData.map(r => r.date),
                datasets: [{
                    label: 'OT Hours', data: otData.map(r => r.ot_hours),
                    backgroundColor: 'rgba(245,158,11,0.6)', borderRadius: 4, yAxisID: 'y'
                }, {
                    type: 'line', label: 'Employees with OT',
                    data: otData.map(r => r.ot_count),
                    borderColor: palette.red.border, backgroundColor: palette.red.bg,
                    tension: 0.3, pointRadius: 3, yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: {
                    y:  { beginAtZero: true, position: 'left',  title: { display: true, text: 'Hours' } },
                    y1: { beginAtZero: true, position: 'right', title: { display: true, text: 'Count' },
                        grid: { drawOnChartArea: false }, ticks: { stepSize: 1 } }
                }
            }
        });
    }

    // ── 9. Leave Utilisation Trend (Stacked Area) ──────────
    const leaveTrendData = @json($leaveTrend);
    if (document.getElementById('leaveTrendChart') && leaveTrendData.length) {
        new Chart(document.getElementById('leaveTrendChart'), {
            type: 'line',
            data: {
                labels: leaveTrendData.map(r => r.month),
                datasets: [{
                    label: 'Annual', data: leaveTrendData.map(r => parseFloat(r.annual)),
                    borderColor: palette.blue.border, backgroundColor: palette.blue.bg,
                    fill: true, tension: 0.3
                }, {
                    label: 'Medical', data: leaveTrendData.map(r => parseFloat(r.medical)),
                    borderColor: palette.red.border, backgroundColor: palette.red.bg,
                    fill: true, tension: 0.3
                }, {
                    label: 'Emergency', data: leaveTrendData.map(r => parseFloat(r.emergency)),
                    borderColor: palette.amber.border, backgroundColor: palette.amber.bg,
                    fill: true, tension: 0.3
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: { y: { beginAtZero: true, stacked: true, ticks: { stepSize: 1 } }, x: { stacked: true } }
            }
        });
    }

    // ── 10. Leave Distribution Doughnut (Tab 5) ────────────
    new Chart(document.getElementById('leaveDistChart'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(leaveData).map(k => leaveLabels[k] || k),
            datasets: [{
                data: Object.values(leaveData),
                backgroundColor: ['rgba(59,130,246,0.8)', 'rgba(239,68,68,0.8)', 'rgba(251,191,36,0.8)', 'rgba(107,114,128,0.8)'],
                borderWidth: 2, borderColor: '#fff'
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '60%',
            plugins: { legend: { position: 'bottom' } }
        }
    });
});
</script>
@endsection
