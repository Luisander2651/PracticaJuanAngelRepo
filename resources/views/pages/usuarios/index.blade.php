@extends('layouts.admin')

@section('title', 'Usuarios - Dentissa')

@section('content')
<div class="space-y-6">
    {{-- Header Section --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <x-ui.h1 as="h2" class="text-xl! text-slate-900">Usuarios</x-ui.h1>
            <p class="mt-2 text-sm text-slate-500">Administra el acceso y roles del personal de la clinica.</p>
        </div>

        <button type="button" data-create-user-open class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#E91E63] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#d61b5b]">
            + Nuevo usuario
        </button>
    </div>

    {{-- Counters Section --}}
    <div class="grid gap-3 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Total</p>
            <p class="mt-2 text-xl font-semibold text-slate-900" data-users-count>0</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Administradores</p>
            <p class="mt-2 text-xl font-semibold text-[#B5114A]" data-users-admin-count>0</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Asistentes</p>
            <p class="mt-2 text-xl font-semibold text-sky-600" data-users-asistent-count>0</p>
        </div>
    </div>

    {{-- Info Card --}}
    <article class="rounded-3xl border border-[#F5C2D6] bg-[#FFF7FA] p-5">
        <p class="text-sm font-semibold text-[#B5114A]">Gestion de personal</p>
        <p class="mt-2 text-sm leading-6 text-slate-600">Puedes crear cuentas para el personal, asignar roles de administrador o asistente, y controlar su acceso al sistema.</p>
    </article>

    {{-- Feedback and List --}}
    <div id="users-error" class="hidden rounded-3xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></div>
    
    <div id="users-loading" class="hidden rounded-3xl border border-slate-200 bg-white p-5 text-sm text-slate-600 shadow-sm">
        Cargando usuarios...
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3" data-users-list id="users-table"></div>

    <div id="users-empty" class="hidden rounded-3xl border border-slate-200 bg-white p-5 text-sm text-slate-500 shadow-sm">
        No hay usuarios registrados para mostrar.
    </div>

    {{-- Modals --}}
    <x-ui.confirm-delete-modal
        modal-id="users-delete-modal"
        message-prefix="Esta segura que desea eliminar"
    />

    <x-ui.edit-user-modal
        modal-id="users-edit-modal"
    />

    <x-ui.create-user-modal
        modal-id="users-create-modal"
    />
</div>

<script>
    (function () {
        const errorBox = document.getElementById('users-error');
        const loadingBox = document.getElementById('users-loading');
        const emptyBox = document.getElementById('users-empty');
        const userList = document.querySelector('[data-users-list]');
        
        const countAll = document.querySelector('[data-users-count]');
        const countAdmin = document.querySelector('[data-users-admin-count]');
        const countAsistent = document.querySelector('[data-users-asistent-count]');

        function showError(message) {
            if (!errorBox) return;
            errorBox.textContent = message;
            errorBox.classList.remove('hidden');
        }

        function hideError() {
            if (!errorBox) return;
            errorBox.textContent = '';
            errorBox.classList.add('hidden');
        }

        function setLoading(isLoading) {
            if (loadingBox) loadingBox.classList.toggle('hidden', !isLoading);
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function normalizeRole(roleId) {
            switch (roleId) {
                case "admin": return 'Administrador';
                case "asistent": return 'Asistente';
                default: return roleId;
            }
        }

        function normalizeStatus(status) {
            switch (status) {
                case "active": return 'Activo';
                case "inactive": return 'Inactivo';
                default: return status;
            }
        }

        function renderUserCards(records) {
            if (!userList) return;

            let adminCount = 0;
            let asistentCount = 0;

            records.forEach(user => {
                if (user.role_id === 'admin') adminCount++;
                else if (user.role_id === 'asistent') asistentCount++;
            });

            if (countAll) countAll.textContent = String(records.length);
            if (countAdmin) countAdmin.textContent = String(adminCount);
            if (countAsistent) countAsistent.textContent = String(asistentCount);

            if (emptyBox) emptyBox.classList.toggle('hidden', records.length !== 0);

            userList.innerHTML = records.map(function (user) {
                const id = user.id ?? '';
                const firstName = user.first_name ?? '';
                const lastName = user.last_name ?? '';
                const fullName = `${firstName} ${lastName}`.trim();
                const email = user.email ?? 'Sin correo';
                const roleId = user.role_id ?? '';
                const roleLabel = normalizeRole(roleId);
                const status = user.status ?? '';
                const statusLabel = normalizeStatus(status);
                
                const statusClasses = status === 'active'
                    ? 'bg-emerald-50 text-emerald-700'
                    : 'bg-red-50 text-red-700';

                const roleClasses = roleId === 'admin'
                    ? 'bg-[#FDF1F6] text-[#B5114A]'
                    : 'bg-sky-50 text-sky-700';

                const initial = (firstName.charAt(0) || lastName.charAt(0) || 'U').toUpperCase();

                return [
                    '<article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">',
                        '<div class="flex items-start justify-between gap-4">',
                            '<div class="flex items-center gap-3">',
                                '<div class="flex h-12 w-12 items-center justify-center rounded-full ' + roleClasses + ' text-sm font-semibold">', escapeHtml(initial), '</div>',
                                '<div>',
                                    '<h3 class="text-base font-semibold text-slate-900 break-words">', escapeHtml(fullName), '</h3>',
                                    '<p class="text-xs font-medium ' + (roleId === 'admin' ? 'text-[#B5114A]' : 'text-sky-700') + '">', escapeHtml(roleLabel), '</p>',
                                '</div>',
                            '</div>',
                            '<span class="shrink-0 rounded-full px-3 py-1 text-xs font-semibold ' + statusClasses + '">', escapeHtml(statusLabel), '</span>',
                        '</div>',
                        
                        '<div class="mt-4 grid gap-2">',
                            '<div class="flex items-center gap-2 rounded-2xl bg-slate-50 px-4 py-3 text-xs text-slate-600">',
                                '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>',
                                '<span class="break-all">', escapeHtml(email), '</span>',
                            '</div>',
                        '</div>',

                        '<div class="mt-4 flex flex-wrap gap-2">',
                            '<button type="button" data-user-edit data-user-id="', escapeHtml(id), '" data-user-first-name="', escapeHtml(firstName), '" data-user-last-name="', escapeHtml(lastName), '" data-user-role-id="', escapeHtml(roleId), '" data-user-status="', escapeHtml(status), '" class="rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">Editar</button>',
                            '<button type="button" data-user-delete data-user-id="', escapeHtml(id), '" data-user-label="', escapeHtml(fullName), '" class="rounded-full border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-700 transition hover:bg-red-50">Eliminar</button>',
                        '</div>',
                    '</article>'
                ].join('');
            }).join('');
        }

        async function loadUsers() {
            hideError();
            setLoading(true);

            try {
                const response = await fetch('{{ url('/api/v1/users') }}', {
                    method: 'GET',
                    credentials: 'include',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const message = payload.error || payload.message || 'No se pudieron cargar los usuarios.';
                    showError(message);
                    renderUserCards([]);
                    return;
                }

                const records = Array.isArray(payload?.data) ? payload.data : (Array.isArray(payload) ? payload : []);
                renderUserCards(records);
            } catch (error) {
                showError('Error de conexion. Intentalo de nuevo.');
                renderUserCards([]);
            } finally {
                setLoading(false);
            }
        }

        window.usersPage = {
            reload: loadUsers,
            showError: showError,
            hideError: hideError,
        };

        document.addEventListener('DOMContentLoaded', loadUsers);
    })();
</script>

@vite('resources/js/pages/usuarios/delete-user.js')
@vite('resources/js/pages/usuarios/edit-user.js')
@vite('resources/js/pages/usuarios/create-user.js')
@endsection
