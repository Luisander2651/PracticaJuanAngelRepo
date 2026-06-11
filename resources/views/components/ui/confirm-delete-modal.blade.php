@props([
    'modalId' => 'confirm-delete-modal',
    'title' => 'Confirmar eliminacion',
    'messagePrefix' => 'Esta segura que desea eliminar',
    'confirmText' => 'Eliminar',
    'cancelText' => 'Cancelar',
])

<div
    id="{{ $modalId }}"
    data-confirm-modal
    class="fixed inset-0 z-100 hidden items-center justify-center bg-slate-900/50 p-4"
>
    <div class="w-full max-w-md rounded-[1.75rem] border border-slate-200 bg-white shadow-xl">
        <div class="border-b border-slate-200 px-6 py-5">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Confirmacion</p>
            <h3 class="mt-2 text-lg font-semibold text-slate-900">{{ $title }}</h3>
        </div>

        <div class="px-6 py-5">
            <div class="rounded-2xl border border-red-200 bg-red-50 p-4">
                <p class="text-sm leading-6 text-red-800">
                    {{ $messagePrefix }}
                    <span data-confirm-target class="font-semibold text-red-900"></span>
                    de forma permanente.
                </p>
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-6 py-5">
            <button
                type="button"
                data-confirm-cancel
                class="cursor-pointer rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
            >
                {{ $cancelText }}
            </button>
            <button
                type="button"
                data-confirm-accept
                class="cursor-pointer rounded-xl bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-700"
            >
                {{ $confirmText }}
            </button>
        </div>
    </div>
</div>
