@extends('admin.layouts.app')

@section('title', 'Doctor Prescription Settings')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white/90">Prescription Settings</h1>
            <p class="text-sm text-white/50 mt-1">Manage header, footer and layout settings for prescriptions</p>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex items-center gap-1 p-1 glass-card-static mb-6 w-fit">
        <a href="{{ route('admin.prescription-settings.headers') }}" class="px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.prescription-settings.headers*') ? 'bg-indigo-500/20 text-indigo-400' : 'text-white/60 hover:text-white/80 hover:bg-white/5' }}">
            Headers
        </a>
        <a href="{{ route('admin.prescription-settings.footers') }}" class="px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.prescription-settings.footers*') ? 'bg-indigo-500/20 text-indigo-400' : 'text-white/60 hover:text-white/80 hover:bg-white/5' }}">
            Footers
        </a>
        <a href="{{ route('admin.prescription-settings.doctors') }}" class="px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.prescription-settings.doctors*') ? 'bg-indigo-500/20 text-indigo-400' : 'text-white/60 hover:text-white/80 hover:bg-white/5' }}">
            Doctor Settings
        </a>
    </div>

    @if(session('success'))
        <div data-flash-success="{{ session('success') }}"></div>
    @endif
    @if(session('error'))
        <div data-flash-error="{{ session('error') }}"></div>
    @endif

    @php
        $paperSizes = ['A4' => 'A4 (210×297mm)', 'A5' => 'A5 (148×210mm)', 'Letter' => 'Letter (216×279mm)', 'Custom' => 'Custom'];
    @endphp

    <div class="glass-card-static overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/10">
                        <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase">Doctor</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-white/50 uppercase w-32">Header</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase">Header Template</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-white/50 uppercase w-32">Footer</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase">Footer Template</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-white/50 uppercase w-32">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($doctors as $doctor)
                        @php $setting = $doctor->prescriptionSetting; @endphp
                        <tbody x-data="{
                            layoutOpen: false,
                            headerOn: {{ $setting && $setting->header_enabled ? 'true' : 'false' }},
                            footerOn: {{ $setting && $setting->footer_enabled ? 'true' : 'false' }},
                            headerId: '{{ $setting->header_id ?? '' }}',
                            footerId: '{{ $setting->footer_id ?? '' }}',
                            ps: '{{ $setting->paper_size ?? 'A4' }}',
                            cw: '{{ $setting->paper_width_mm ?? 210 }}',
                            ch: '{{ $setting->paper_height_mm ?? 297 }}',
                            hmt: '{{ $setting->header_margin_top_mm ?? 0 }}',
                            hmr: '{{ $setting->header_margin_right_mm ?? 0 }}',
                            hmb: '{{ $setting->header_margin_bottom_mm ?? 0 }}',
                            hml: '{{ $setting->header_margin_left_mm ?? 0 }}',
                            hpt: '{{ $setting->header_padding_top_mm ?? 5 }}',
                            hpr: '{{ $setting->header_padding_right_mm ?? 9 }}',
                            hpb: '{{ $setting->header_padding_bottom_mm ?? 5 }}',
                            hpl: '{{ $setting->header_padding_left_mm ?? 9 }}',
                            fmt: '{{ $setting->footer_margin_top_mm ?? 0 }}',
                            fmr: '{{ $setting->footer_margin_right_mm ?? 0 }}',
                            fmb: '{{ $setting->footer_margin_bottom_mm ?? 0 }}',
                            fml: '{{ $setting->footer_margin_left_mm ?? 0 }}',
                            fpt: '{{ $setting->footer_padding_top_mm ?? 4 }}',
                            fpr: '{{ $setting->footer_padding_right_mm ?? 7 }}',
                            fpb: '{{ $setting->footer_padding_bottom_mm ?? 4 }}',
                            fpl: '{{ $setting->footer_padding_left_mm ?? 7 }}',
                            get pageW() {
                                if (this.ps === 'Custom') return parseFloat(this.cw) || 210;
                                return { A4: 210, A5: 148, Letter: 216 }[this.ps] || 210;
                            },
                            get pageH() {
                                if (this.ps === 'Custom') return parseFloat(this.ch) || 297;
                                return { A4: 297, A5: 210, Letter: 279 }[this.ps] || 297;
                            },
                            post(url, data) {
                                const form = document.createElement('form');
                                form.method = 'POST';
                                form.action = url;
                                const token = document.querySelector('meta[name=csrf-token]');
                                if (token) {
                                    const t = document.createElement('input');
                                    t.type = 'hidden'; t.name = '_token'; t.value = token.content;
                                    form.appendChild(t);
                                }
                                const method = document.createElement('input');
                                method.type = 'hidden'; method.name = '_method'; method.value = 'PATCH';
                                form.appendChild(method);
                                for (const [key, val] of Object.entries(data)) {
                                    const inp = document.createElement('input');
                                    inp.type = 'hidden'; inp.name = key; inp.value = val ?? '';
                                    form.appendChild(inp);
                                }
                                document.body.appendChild(form);
                                form.submit();
                            },
                            saveMain() {
                                this.post('{{ route('admin.prescription-settings.doctors.update', $doctor->id) }}', {
                                    header_enabled: this.headerOn ? '1' : '0',
                                    header_id: this.headerId || null,
                                    footer_enabled: this.footerOn ? '1' : '0',
                                    footer_id: this.footerId || null,
                                });
                            },
                            saveLayout() {
                                this.post('{{ route('admin.prescription-settings.doctors.update', $doctor->id) }}', {
                                    paper_size: this.ps,
                                    paper_width_mm: this.cw,
                                    paper_height_mm: this.ch,
                                    header_margin_top_mm: this.hmt,
                                    header_margin_right_mm: this.hmr,
                                    header_margin_bottom_mm: this.hmb,
                                    header_margin_left_mm: this.hml,
                                    header_padding_top_mm: this.hpt,
                                    header_padding_right_mm: this.hpr,
                                    header_padding_bottom_mm: this.hpb,
                                    header_padding_left_mm: this.hpl,
                                    footer_margin_top_mm: this.fmt,
                                    footer_margin_right_mm: this.fmr,
                                    footer_margin_bottom_mm: this.fmb,
                                    footer_margin_left_mm: this.fml,
                                    footer_padding_top_mm: this.fpt,
                                    footer_padding_right_mm: this.fpr,
                                    footer_padding_bottom_mm: this.fpb,
                                    footer_padding_left_mm: this.fpl,
                                });
                            }
                        }">
                            {{-- Main Row --}}
                            <tr class="border-b border-white/5 hover:bg-white/5">
                                <td class="px-4 py-3 text-white/90 font-medium">{{ $doctor->name }}</td>
                                <td class="px-4 py-3 text-center">
                                    <button type="button" @click="headerOn = !headerOn"
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-300 ease-in-out focus:outline-none"
                                        :class="headerOn ? 'bg-indigo-500' : 'bg-white/15'">
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow-lg transition-transform duration-300 ease-in-out"
                                            :class="headerOn ? 'translate-x-[22px]' : 'translate-x-[3px]'"></span>
                                    </button>
                                </td>
                                <td class="px-4 py-3">
                                    <select x-model="headerId" class="w-full glass-input text-xs">
                                        <option value="">-- None --</option>
                                        @foreach($headers as $h)
                                            <option value="{{ $h->id }}">{{ $h->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button type="button" @click="footerOn = !footerOn"
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-300 ease-in-out focus:outline-none"
                                        :class="footerOn ? 'bg-indigo-500' : 'bg-white/15'">
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow-lg transition-transform duration-300 ease-in-out"
                                            :class="footerOn ? 'translate-x-[22px]' : 'translate-x-[3px]'"></span>
                                    </button>
                                </td>
                                <td class="px-4 py-3">
                                    <select x-model="footerId" class="w-full glass-input text-xs">
                                        <option value="">-- None --</option>
                                        @foreach($footers as $f)
                                            <option value="{{ $f->id }}">{{ $f->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button" @click="layoutOpen = !layoutOpen"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-indigo-400 bg-indigo-500/10 hover:bg-indigo-500/20 border border-indigo-500/20 rounded-lg transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            Layout
                                        </button>
                                        <button type="button" @click="saveMain()"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-white/70 bg-white/5 hover:bg-white/10 border border-white/10 rounded-lg transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Save
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            {{-- Layout Settings Row --}}
                            <tr x-show="layoutOpen" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display:none;">
                                <td colspan="6" class="p-0">
                                    <div class="bg-white/[0.02] p-4">
                                        <div class="flex items-center justify-between mb-4">
                                            <h3 class="text-sm font-semibold text-white/80">Layout Settings — {{ $doctor->name }}</h3>
                                            <button type="button" @click="saveLayout()"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-white/70 bg-white/5 hover:bg-white/10 border border-white/10 rounded-lg transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                Save Layout
                                            </button>
                                        </div>

                                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                            {{-- Paper Size --}}
                                            <div class="space-y-3">
                                                <h4 class="text-xs font-semibold text-white/60 uppercase tracking-wider border-b border-white/10 pb-1">Paper Size</h4>
                                                <div>
                                                    <label class="block text-xs text-white/50 mb-1">Size</label>
                                                    <select x-model="ps" class="w-full glass-input text-xs">
                                                        @foreach($paperSizes as $val => $label)
                                                            <option value="{{ $val }}">{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div x-show="ps === 'Custom'" x-cloak x-transition>
                                                    <div class="grid grid-cols-2 gap-2">
                                                        <div>
                                                            <label class="block text-xs text-white/50 mb-1">Width (mm)</label>
                                                            <input type="number" x-model="cw" min="50" max="1000" step="0.1" class="w-full glass-input text-xs">
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs text-white/50 mb-1">Height (mm)</label>
                                                            <input type="number" x-model="ch" min="50" max="1500" step="0.1" class="w-full glass-input text-xs">
                                                        </div>
                                                    </div>
                                                </div>
                                                {{-- Live Preview --}}
                                                <div class="mt-3">
                                                    <label class="block text-xs text-white/50 mb-2">Live Preview</label>
                                                    <div class="relative bg-white/5 border border-white/10 rounded p-2" style="aspect-ratio: 210/297; max-height: 200px;">
                                                        <div class="absolute inset-0 flex flex-col text-[6px] text-white/40">
                                                            <div class="bg-indigo-500/10 border-b border-dashed border-indigo-500/30 flex items-center justify-center"
                                                                :style="'height:' + Math.min((parseFloat(hmt)||0) / (pageH||297) * 100, 30) + '%'">
                                                                <span x-show="parseFloat(hmt) > 0" class="text-[5px]">M <span x-text="hmt"></span>mm</span>
                                                            </div>
                                                            <div class="flex flex-1 min-h-0">
                                                                <div class="bg-indigo-500/10 border-r border-dashed border-indigo-500/30 flex items-center justify-center"
                                                                    :style="'width:' + Math.min((parseFloat(hml)||0) / (pageW||210) * 100, 30) + '%'"></div>
                                                                <div class="flex-1 flex flex-col min-w-0">
                                                                    <div class="bg-emerald-500/10 border-b border-dashed border-emerald-500/30 flex items-center justify-center"
                                                                        :style="'height:' + Math.min((parseFloat(hpt)||0) / (pageH||297) * 100, 20) + '%'">
                                                                        <span x-show="parseFloat(hpt) > 0" class="text-[5px]">P <span x-text="hpt"></span>mm</span>
                                                                    </div>
                                                                    <div class="bg-emerald-500/20 flex items-center justify-center text-[7px] text-emerald-400 font-medium border-b border-white/10" style="min-height:20px;">HEADER</div>
                                                                    <div class="bg-emerald-500/10 border-b border-dashed border-emerald-500/30 flex items-center justify-center"
                                                                        :style="'height:' + Math.min((parseFloat(hpb)||0) / (pageH||297) * 100, 20) + '%'">
                                                                        <span x-show="parseFloat(hpb) > 0" class="text-[5px]">P <span x-text="hpb"></span>mm</span>
                                                                    </div>
                                                                    <div class="flex-1 bg-white/5 flex items-center justify-center text-[7px] text-white/30">BODY</div>
                                                                    <div class="bg-amber-500/10 border-t border-dashed border-amber-500/30 flex items-center justify-center"
                                                                        :style="'height:' + Math.min((parseFloat(fpt)||0) / (pageH||297) * 100, 20) + '%'">
                                                                        <span x-show="parseFloat(fpt) > 0" class="text-[5px]">P <span x-text="fpt"></span>mm</span>
                                                                    </div>
                                                                    <div class="bg-amber-500/20 flex items-center justify-center text-[7px] text-amber-400 font-medium border-t border-white/10" style="min-height:18px;">FOOTER</div>
                                                                    <div class="bg-amber-500/10 border-t border-dashed border-amber-500/30 flex items-center justify-center"
                                                                        :style="'height:' + Math.min((parseFloat(fpb)||0) / (pageH||297) * 100, 20) + '%'">
                                                                        <span x-show="parseFloat(fpb) > 0" class="text-[5px]">P <span x-text="fpb"></span>mm</span>
                                                                    </div>
                                                                </div>
                                                                <div class="bg-indigo-500/10 border-l border-dashed border-indigo-500/30 flex items-center justify-center"
                                                                    :style="'width:' + Math.min((parseFloat(hmr)||0) / (pageW||210) * 100, 30) + '%'"></div>
                                                            </div>
                                                            <div class="bg-indigo-500/10 border-t border-dashed border-indigo-500/30 flex items-center justify-center"
                                                                :style="'height:' + Math.min((parseFloat(fmb)||0) / (pageH||297) * 100, 30) + '%'">
                                                                <span x-show="parseFloat(fmb) > 0" class="text-[5px]">M <span x-text="fmb"></span>mm</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="text-[10px] text-white/40 mt-1 text-center">
                                                        <span x-text="pageW"></span>mm &times; <span x-text="pageH"></span>mm
                                                        <template x-if="ps === 'Custom'"><span> (Custom)</span></template>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Header Layout --}}
                                            <div class="space-y-3">
                                                <h4 class="text-xs font-semibold text-indigo-400 uppercase tracking-wider border-b border-indigo-500/20 pb-1">Header Layout</h4>
                                                <div class="grid grid-cols-2 gap-2">
                                                    <div><label class="block text-xs text-white/50 mb-1">Margin Top (mm)</label><input type="number" x-model="hmt" min="0" max="100" step="0.1" class="w-full glass-input text-xs"></div>
                                                    <div><label class="block text-xs text-white/50 mb-1">Margin Right (mm)</label><input type="number" x-model="hmr" min="0" max="100" step="0.1" class="w-full glass-input text-xs"></div>
                                                    <div><label class="block text-xs text-white/50 mb-1">Margin Bottom (mm)</label><input type="number" x-model="hmb" min="0" max="100" step="0.1" class="w-full glass-input text-xs"></div>
                                                    <div><label class="block text-xs text-white/50 mb-1">Margin Left (mm)</label><input type="number" x-model="hml" min="0" max="100" step="0.1" class="w-full glass-input text-xs"></div>
                                                </div>
                                                <div class="grid grid-cols-2 gap-2">
                                                    <div><label class="block text-xs text-white/50 mb-1">Padding Top (mm)</label><input type="number" x-model="hpt" min="0" max="100" step="0.1" class="w-full glass-input text-xs"></div>
                                                    <div><label class="block text-xs text-white/50 mb-1">Padding Right (mm)</label><input type="number" x-model="hpr" min="0" max="100" step="0.1" class="w-full glass-input text-xs"></div>
                                                    <div><label class="block text-xs text-white/50 mb-1">Padding Bottom (mm)</label><input type="number" x-model="hpb" min="0" max="100" step="0.1" class="w-full glass-input text-xs"></div>
                                                    <div><label class="block text-xs text-white/50 mb-1">Padding Left (mm)</label><input type="number" x-model="hpl" min="0" max="100" step="0.1" class="w-full glass-input text-xs"></div>
                                                </div>
                                            </div>

                                            {{-- Footer Layout --}}
                                            <div class="space-y-3">
                                                <h4 class="text-xs font-semibold text-amber-400 uppercase tracking-wider border-b border-amber-500/20 pb-1">Footer Layout</h4>
                                                <div class="grid grid-cols-2 gap-2">
                                                    <div><label class="block text-xs text-white/50 mb-1">Margin Top (mm)</label><input type="number" x-model="fmt" min="0" max="100" step="0.1" class="w-full glass-input text-xs"></div>
                                                    <div><label class="block text-xs text-white/50 mb-1">Margin Right (mm)</label><input type="number" x-model="fmr" min="0" max="100" step="0.1" class="w-full glass-input text-xs"></div>
                                                    <div><label class="block text-xs text-white/50 mb-1">Margin Bottom (mm)</label><input type="number" x-model="fmb" min="0" max="100" step="0.1" class="w-full glass-input text-xs"></div>
                                                    <div><label class="block text-xs text-white/50 mb-1">Margin Left (mm)</label><input type="number" x-model="fml" min="0" max="100" step="0.1" class="w-full glass-input text-xs"></div>
                                                </div>
                                                <div class="grid grid-cols-2 gap-2">
                                                    <div><label class="block text-xs text-white/50 mb-1">Padding Top (mm)</label><input type="number" x-model="fpt" min="0" max="100" step="0.1" class="w-full glass-input text-xs"></div>
                                                    <div><label class="block text-xs text-white/50 mb-1">Padding Right (mm)</label><input type="number" x-model="fpr" min="0" max="100" step="0.1" class="w-full glass-input text-xs"></div>
                                                    <div><label class="block text-xs text-white/50 mb-1">Padding Bottom (mm)</label><input type="number" x-model="fpb" min="0" max="100" step="0.1" class="w-full glass-input text-xs"></div>
                                                    <div><label class="block text-xs text-white/50 mb-1">Padding Left (mm)</label><input type="number" x-model="fpl" min="0" max="100" step="0.1" class="w-full glass-input text-xs"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-white/40">No doctors found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($doctors->hasPages())
        <div class="mt-4 flex justify-center">
            {{ $doctors->links() }}
        </div>
    @endif
@endsection
