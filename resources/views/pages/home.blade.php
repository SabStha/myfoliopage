@extends('layouts.public')
@section('title','Home')
@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <!-- Left profile panel -->
    <aside class="lg:col-span-3">
        <div class="rounded-2xl border border-amber-200 bg-white p-5 shadow">
            <div class="flex items-center gap-3">
                <div class="h-16 w-16 rounded-full bg-gradient-to-br from-amber-600 to-orange-500"></div>
                <div>
                    <div class="font-semibold">Your Name</div>
                    <div class="text-sm text-slate-500">Full‑Stack Developer</div>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2">
                <a href="#" class="h-8 w-8 rounded-full bg-amber-50 text-amber-600 grid place-content-center">in</a>
                <a href="#" class="h-8 w-8 rounded-full bg-amber-50 text-amber-600 grid place-content-center">gh</a>
                <a href="#" class="h-8 w-8 rounded-full bg-amber-50 text-amber-600 grid place-content-center">x</a>
            </div>
            <div class="mt-6 text-xs uppercase text-slate-500">Skills</div>
            <div class="mt-2 space-y-3">
                @foreach(($skills ?? []) as $skill)
                    <div>
                        <div class="flex justify-between text-sm"><span>{{ $skill->name }}</span><span class="text-slate-500">{{ $skill->level }}</span></div>
                        <div class="h-2 rounded-full bg-slate-200"><div class="h-2 rounded-full bg-amber-500" style="width: 80%"></div></div>
                    </div>
                @endforeach
            </div>
            <div class="mt-6">
                <a href="#" class="inline-flex items-center rounded-xl border border-amber-300 bg-amber-50 px-4 py-2 text-sm text-amber-700 hover:bg-amber-100">Download CV</a>
            </div>
        </div>
    </aside>

    <!-- Main content -->
    <section class="lg:col-span-9">
        <div class="rounded-2xl border border-amber-200 bg-white p-8 shadow">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                <div>
                    <h1 class="text-3xl font-extrabold leading-tight">I'm <span class="text-slate-900">Your Name</span><br><span class="bg-gradient-to-r from-amber-600 to-orange-500 bg-clip-text text-transparent">Front‑end</span> Developer</h1>
                    <p class="mt-3 text-slate-600">Short intro about you, your stack and what you love building.</p>
                    <div class="mt-5 flex gap-3">
                        <x-ui.button>Hire Me</x-ui.button>
                        <x-ui.button variant="outline">Contact</x-ui.button>
                    </div>
                </div>
                <div class="md:justify-self-end">
                    <div class="h-48 w-48 rounded-2xl bg-gradient-to-br from-amber-600 to-orange-500"></div>
                </div>
            </div>
        </div>

        <div class="mt-6 rounded-2xl border border-amber-200 bg-white p-8 shadow">
            <h2 class="text-xl font-semibold text-center">My Services</h2>
            <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach([['Web Development','Blog, E‑Commerce'],['UI/UX Design','Mobile/Web'],['Sound Design','Voice/Beats'],['Game Design','Props & Objects'],['Photography','Product'],['Advertising','Campaigns']] as $svc)
                    <div class="rounded-xl border border-slate-200 p-5 hover:shadow">
                        <div class="h-10 w-10 rounded-lg bg-amber-100 text-amber-600 grid place-content-center mb-3">★</div>
                        <div class="font-semibold">{{ $svc[0] }}</div>
                        <div class="text-sm text-slate-500">{{ $svc[1] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</div>
@endsection


