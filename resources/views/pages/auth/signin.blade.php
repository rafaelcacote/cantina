@extends('layouts.fullscreen-layout')

@section('content')
    <style>
        .login-screen {
            --login-red: #e11d2e;
            --login-ink: #1a1410;
            --login-muted: #6b5e55;
            --login-line: #eadfd6;
            --login-soft: #fff8f5;
            font-family: Outfit, sans-serif;
            display: flex;
            width: 100%;
            min-height: 100vh;
            background: #fff;
        }

        .login-panel {
            position: relative;
            display: flex;
            flex-direction: column;
            width: 100%;
            min-height: 100vh;
            padding: 1.5rem 1.5rem 3.5rem;
            background:
                radial-gradient(ellipse 80% 50% at 0% 0%, rgba(225, 29, 46, 0.05), transparent 55%),
                linear-gradient(165deg, #ffffff 0%, var(--login-soft) 100%);
        }

        .login-panel-inner {
            display: flex;
            flex: 1;
            flex-direction: column;
            justify-content: center;
            width: 100%;
            max-width: 26rem;
            margin: auto;
        }

        .login-brand {
            display: flex;
            justify-content: center;
            margin-bottom: 1.75rem;
        }

        .login-brand img {
            height: 9rem;
            width: auto;
        }

        .login-eyebrow {
            margin: 0 0 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            color: var(--login-red);
        }

        .login-title {
            margin: 0;
            font-size: 2rem;
            font-weight: 700;
            line-height: 1.15;
            letter-spacing: -0.02em;
            color: var(--login-ink);
        }

        .login-subtitle {
            margin: 0.75rem 0 0;
            font-size: 1rem;
            line-height: 1.55;
            color: var(--login-muted);
        }

        .login-copy {
            margin-bottom: 2rem;
        }

        .login-alert {
            margin-bottom: 1.25rem;
            border: 1px solid #fecaca;
            border-radius: 0.75rem;
            background: #fef2f2;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            color: #b91c1c;
        }

        .login-field {
            margin-bottom: 1.25rem;
        }

        .login-label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--login-ink);
        }

        .login-input {
            width: 100%;
            height: 3rem;
            border: 1px solid var(--login-line);
            border-radius: 0.75rem;
            background: rgba(255, 255, 255, 0.9);
            padding: 0 1rem;
            font-size: 0.9375rem;
            color: var(--login-ink);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .login-input::placeholder {
            color: #a8988c;
        }

        .login-input:focus {
            border-color: var(--login-red);
            box-shadow: 0 0 0 4px rgba(225, 29, 46, 0.12);
            outline: none;
            background: #fff;
        }

        .login-password {
            position: relative;
        }

        .login-password .login-input {
            padding-right: 3rem;
        }

        .login-toggle {
            position: absolute;
            top: 50%;
            right: 0.75rem;
            z-index: 2;
            display: inline-flex;
            transform: translateY(-50%);
            border: 0;
            border-radius: 0.5rem;
            background: transparent;
            padding: 0.375rem;
            color: #8a7a6f;
            cursor: pointer;
        }

        .login-toggle:hover {
            background: rgba(0, 0, 0, 0.05);
            color: var(--login-ink);
        }

        .login-submit {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 3rem;
            margin-top: 0.5rem;
            border: 0;
            border-radius: 0.75rem;
            background: linear-gradient(180deg, #ef2a3b 0%, var(--login-red) 100%);
            box-shadow: 0 10px 24px -12px rgba(225, 29, 46, 0.65);
            font-size: 0.9375rem;
            font-weight: 600;
            color: #fff;
            cursor: pointer;
            transition: transform 0.15s ease, filter 0.2s ease, box-shadow 0.2s ease;
        }

        .login-submit:hover {
            filter: brightness(1.05);
            transform: translateY(-1px);
            box-shadow: 0 14px 28px -12px rgba(225, 29, 46, 0.7);
        }

        .login-submit:active {
            transform: translateY(0);
        }

        .login-submit:disabled {
            cursor: wait;
            filter: brightness(0.95);
            transform: none;
        }

        .login-submit-content {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.625rem;
        }

        .login-spinner {
            width: 1.125rem;
            height: 1.125rem;
            border: 2px solid rgba(255, 255, 255, 0.35);
            border-top-color: #fff;
            border-radius: 50%;
            animation: login-spin 0.7s linear infinite;
        }

        @keyframes login-spin {
            to {
                transform: rotate(360deg);
            }
        }

        .login-footer {
            position: absolute;
            bottom: 1.25rem;
            left: 1.5rem;
            right: 1.5rem;
            margin: 0;
            text-align: center;
            font-size: 0.875rem;
            color: var(--login-muted);
        }

        .login-visual {
            display: none;
            position: relative;
            overflow: hidden;
        }

        .login-visual img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .login-visual::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.18), transparent 45%);
            pointer-events: none;
        }

        @media (min-width: 1024px) {
            .login-screen {
                flex-direction: row;
                height: 100vh;
                min-height: 100vh;
                overflow: hidden;
            }

            .login-panel {
                width: 44%;
                flex: 0 0 44%;
                min-height: 100%;
                height: 100%;
                padding: 2rem 3.5rem 3.75rem;
            }

            .login-brand img {
                height: 10rem;
            }

            .login-footer {
                left: 3.5rem;
                right: 3.5rem;
                bottom: 1.5rem;
            }

            .login-title {
                font-size: 2.25rem;
            }

            .login-visual {
                display: block;
                flex: 1 1 56%;
                width: 56%;
                height: 100%;
                min-height: 100%;
            }
        }

        @media (min-width: 1280px) {
            .login-panel {
                width: 42%;
                flex: 0 0 42%;
                padding: 2.5rem 4.5rem 3.75rem;
            }

            .login-footer {
                left: 4.5rem;
                right: 4.5rem;
                bottom: 1.75rem;
            }

            .login-visual {
                width: 58%;
                flex-basis: 58%;
            }
        }
    </style>

    <div class="login-screen">
        <section class="login-panel">
            <div class="login-panel-inner">
                <header class="login-brand">
                    <img src="{{ asset('images/logo.png') }}" alt="Bablif's" />
                </header>

                <div class="login-copy">
                    <p class="login-eyebrow">Cantina escolar</p>
                    <h1 class="login-title">Bem-vindo de volta</h1>
                    <p class="login-subtitle">Acesse o painel da Bablif's com seu e-mail e senha.</p>
                </div>

                @if ($errors->any())
                    <div class="login-alert" role="alert">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form
                    method="POST"
                    action="{{ route('signin.store') }}"
                    x-data="{ loading: false }"
                    @submit="loading = true"
                >
                    @csrf

                    <div class="login-field">
                        <label for="email" class="login-label">E-mail</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="seu@email.com"
                            autocomplete="email"
                            required
                            autofocus
                            class="login-input"
                        />
                    </div>

                    <div class="login-field">
                        <label for="password" class="login-label">Senha</label>
                        <div class="login-password" x-data="{ showPassword: false }">
                            <input
                                :type="showPassword ? 'text' : 'password'"
                                id="password"
                                name="password"
                                placeholder="Digite sua senha"
                                autocomplete="current-password"
                                required
                                class="login-input"
                            />
                            <button
                                type="button"
                                class="login-toggle"
                                @click="showPassword = !showPassword"
                                :aria-label="showPassword ? 'Ocultar senha' : 'Mostrar senha'"
                            >
                                <svg x-show="!showPassword" width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M10.0002 13.8619C7.23361 13.8619 4.86803 12.1372 3.92328 9.70241C4.86804 7.26761 7.23361 5.54297 10.0002 5.54297C12.7667 5.54297 15.1323 7.26762 16.0771 9.70243C15.1323 12.1372 12.7667 13.8619 10.0002 13.8619ZM10.0002 4.04297C6.48191 4.04297 3.49489 6.30917 2.4155 9.4593C2.3615 9.61687 2.3615 9.78794 2.41549 9.94552C3.49488 13.0957 6.48191 15.3619 10.0002 15.3619C13.5184 15.3619 16.5055 13.0957 17.5849 9.94555C17.6389 9.78797 17.6389 9.6169 17.5849 9.45932C16.5055 6.30919 13.5184 4.04297 10.0002 4.04297ZM9.99151 7.84413C8.96527 7.84413 8.13333 8.67606 8.13333 9.70231C8.13333 10.7286 8.96527 11.5605 9.99151 11.5605H10.0064C11.0326 11.5605 11.8646 10.7286 11.8646 9.70231C11.8646 8.67606 11.0326 7.84413 10.0064 7.84413H9.99151Z" fill="currentColor" />
                                </svg>
                                <svg x-show="showPassword" width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true" style="display: none;">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M4.63803 3.57709C4.34513 3.2842 3.87026 3.2842 3.57737 3.57709C3.28447 3.86999 3.28447 4.34486 3.57737 4.63775L4.85323 5.91362C3.74609 6.84199 2.89363 8.06395 2.4155 9.45936C2.3615 9.61694 2.3615 9.78801 2.41549 9.94558C3.49488 13.0957 6.48191 15.3619 10.0002 15.3619C11.255 15.3619 12.4422 15.0737 13.4994 14.5598L15.3625 16.4229C15.6554 16.7158 16.1302 16.7158 16.4231 16.4229C16.716 16.13 16.716 15.6551 16.4231 15.3622L4.63803 3.57709ZM12.3608 13.4212L10.4475 11.5079C10.3061 11.5423 10.1584 11.5606 10.0064 11.5606H9.99151C8.96527 11.5606 8.13333 10.7286 8.13333 9.70237C8.13333 9.5461 8.15262 9.39434 8.18895 9.24933L5.91885 6.97923C5.03505 7.69015 4.34057 8.62704 3.92328 9.70247C4.86803 12.1373 7.23361 13.8619 10.0002 13.8619C10.8326 13.8619 11.6287 13.7058 12.3608 13.4212ZM16.0771 9.70249C15.7843 10.4569 15.3552 11.1432 14.8199 11.7311L15.8813 12.7925C16.6329 11.9813 17.2187 11.0143 17.5849 9.94561C17.6389 9.78803 17.6389 9.61696 17.5849 9.45938C16.5055 6.30925 13.5184 4.04303 10.0002 4.04303C9.13525 4.04303 8.30244 4.17999 7.52218 4.43338L8.75139 5.66259C9.1556 5.58413 9.57311 5.54303 10.0002 5.54303C12.7667 5.54303 15.1323 7.26768 16.0771 9.70249Z" fill="currentColor" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="login-submit" :disabled="loading">
                        <span class="login-submit-content">
                            <span x-show="loading" class="login-spinner" aria-hidden="true"></span>
                            <span x-text="loading ? 'Entrando...' : 'Entrar'">Entrar</span>
                        </span>
                    </button>
                </form>
            </div>

            <footer class="login-footer">© {{ date('Y') }} Bablif's</footer>
        </section>

        <aside class="login-visual" aria-hidden="true">
            <img src="{{ asset('images/login/tela_login.png') }}" alt="" />
        </aside>
    </div>
@endsection
