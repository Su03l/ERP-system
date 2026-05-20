@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'بوابة تسجيل الحضور الذاتية' : 'Self-Service Attendance')

@section('content')
<div class="max-w-3xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-10">
        <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white">
            {{ app()->getLocale() === 'ar' ? 'بوابة الحضور والانصراف' : 'Attendance Kiosk' }}
        </h1>
        <p class="mt-2 text-slate-500 dark:text-slate-400">
            {{ app()->getLocale() === 'ar' ? 'سجل حضورك اليومي بنقرة واحدة.' : 'Log your daily attendance with one click.' }}
        </p>
    </div>

    @if ($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-600 text-sm">
            <ul class="list-disc px-4 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-600 text-sm font-semibold text-center flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white dark:bg-slate-900 shadow-xl shadow-slate-200/50 dark:shadow-none border border-slate-200 dark:border-slate-800 rounded-3xl overflow-hidden relative">
        <div class="absolute top-0 right-0 w-[300px] h-[300px] bg-brand-500/10 rounded-full blur-[80px] -mr-32 -mt-32 pointer-events-none"></div>

        <div class="p-8 sm:p-12 text-center relative z-10">
            <!-- Digital Clock Display -->
            <div class="mb-8">
                <div id="live-clock" class="text-6xl sm:text-7xl font-black text-slate-800 dark:text-slate-100 tracking-tighter" dir="ltr">
                    00:00
                </div>
                <div class="text-slate-500 dark:text-slate-400 font-medium mt-2 tracking-wide uppercase">
                    {{ now()->translatedFormat('l, d F Y') }}
                </div>
            </div>

            @if(!$employee)
                <!-- No Employee Profile Linked -->
                <div class="py-8 text-center text-rose-500">
                    <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <p class="font-bold">{{ app()->getLocale() === 'ar' ? 'لا يوجد ملف موظف مرتبط بحسابك.' : 'No employee profile linked to your account.' }}</p>
                    <p class="text-sm mt-1 text-slate-500">{{ app()->getLocale() === 'ar' ? 'يرجى مراجعة إدارة الموارد البشرية.' : 'Please contact HR department.' }}</p>
                </div>
            @else
                <!-- Action Buttons -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-lg mx-auto mt-10">
                    <!-- Clock In Form -->
                    <form action="{{ route('attendance-records.clock-in') }}" method="POST">
                        @csrf
                        <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                        <input type="hidden" name="clock_at" id="clock_in_time" value="">
                        <input type="hidden" name="ip_address" value="{{ request()->ip() }}">
                        
                        <button type="submit" 
                            onclick="document.getElementById('clock_in_time').value = new Date().toISOString()"
                            @if($todayRecord && $todayRecord->clock_in_at) disabled @endif
                            class="w-full relative group overflow-hidden rounded-2xl h-20 flex items-center justify-center gap-3 font-bold text-xl transition-all {{ ($todayRecord && $todayRecord->clock_in_at) ? 'bg-slate-100 dark:bg-slate-800 text-slate-400 cursor-not-allowed shadow-inner' : 'bg-brand-600 text-white shadow-lg shadow-brand-500/30 hover:shadow-brand-500/50 hover:bg-brand-500' }}">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                            <span>{{ app()->getLocale() === 'ar' ? 'تسجيل حضور' : 'Clock In' }}</span>
                        </button>
                    </form>

                    <!-- Clock Out Form -->
                    <form action="{{ route('attendance-records.clock-out') }}" method="POST">
                        @csrf
                        <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                        <input type="hidden" name="clock_at" id="clock_out_time" value="">
                        <input type="hidden" name="ip_address" value="{{ request()->ip() }}">
                        
                        <button type="submit" 
                            onclick="document.getElementById('clock_out_time').value = new Date().toISOString()"
                            @if(!$todayRecord || !$todayRecord->clock_in_at || $todayRecord->clock_out_at) disabled @endif
                            class="w-full relative group overflow-hidden rounded-2xl h-20 flex items-center justify-center gap-3 font-bold text-xl transition-all {{ (!$todayRecord || !$todayRecord->clock_in_at || $todayRecord->clock_out_at) ? 'bg-slate-100 dark:bg-slate-800 text-slate-400 cursor-not-allowed shadow-inner' : 'bg-slate-800 dark:bg-slate-700 text-white shadow-lg hover:bg-slate-700 dark:hover:bg-slate-600' }}">
                            <span>{{ app()->getLocale() === 'ar' ? 'تسجيل انصراف' : 'Clock Out' }}</span>
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        </button>
                    </form>
                </div>
                
                <!-- Status Bar -->
                <div class="mt-12 bg-slate-50 dark:bg-slate-800/50 rounded-2xl p-6 border border-slate-100 dark:border-slate-800">
                    <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-4">{{ app()->getLocale() === 'ar' ? 'حالة اليوم' : 'Today\'s Status' }}</h3>
                    <div class="flex items-center justify-around">
                        <div class="text-center">
                            <div class="text-xs text-slate-500 mb-1">{{ app()->getLocale() === 'ar' ? 'وقت الحضور' : 'Clock In' }}</div>
                            <div class="font-bold text-lg {{ $todayRecord && $todayRecord->clock_in_at ? 'text-brand-600' : 'text-slate-400' }}">
                                {{ $todayRecord && $todayRecord->clock_in_at ? $todayRecord->clock_in_at->format('H:i') : '--:--' }}
                            </div>
                        </div>
                        <div class="w-px h-10 bg-slate-200 dark:bg-slate-700"></div>
                        <div class="text-center">
                            <div class="text-xs text-slate-500 mb-1">{{ app()->getLocale() === 'ar' ? 'وقت الانصراف' : 'Clock Out' }}</div>
                            <div class="font-bold text-lg {{ $todayRecord && $todayRecord->clock_out_at ? 'text-slate-800 dark:text-slate-200' : 'text-slate-400' }}">
                                {{ $todayRecord && $todayRecord->clock_out_at ? $todayRecord->clock_out_at->format('H:i') : '--:--' }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    function updateClock() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-US', { hour12: false, hour: '2-digit', minute: '2-digit' });
        document.getElementById('live-clock').innerText = timeString;
    }
    
    updateClock();
    setInterval(updateClock, 1000);
</script>
@endsection
