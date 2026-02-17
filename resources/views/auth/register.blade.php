<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro | Spikia</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Tailwind Config -->
    <script>
        tailwind.config = {
          theme: {
            extend: {
              colors: {
                spikiaPurple: '#7C3AED',
                neonBlue: '#4ffcff',
                neonPink: '#ff2fa0'
              },
              boxShadow: {
                glow: '0 0 40px rgba(124,58,237,0.8)',
              },
              animation: {
                glow: 'glow 3s ease-in-out infinite alternate',
              },
              keyframes: {
                glow: {
                  '0%': { boxShadow: '0 0 20px #7C3AED' },
                  '100%': { boxShadow: '0 0 45px #4ffcff' },
                }
              }
            }
          }
        }
    </script>
</head>

<body class="min-h-screen flex items-center justify-center 
             bg-gradient-to-br from-black via-zinc-900 to-black text-white">

    <!-- CARD BORDE ANIMADO -->
    <div class="relative w-full max-w-sm p-[2px] rounded-2xl 
                bg-gradient-to-r from-neonPink via-spikiaPurple to-neonBlue
                animate-glow">

        <!-- CARD INTERIOR -->
        <div class="bg-zinc-900/95 rounded-2xl px-7 py-7 backdrop-blur-xl">

            <!-- LOGO -->
            <div class="flex flex-col items-center mb-5">
                <img 
                    src="{{ asset('images/spikia-25.png') }}"
                    alt="Spikia"
                    class="h-20 mb-1 drop-shadow-[0_0_30px_rgba(124,58,237,0.6)]"
                >
                <h2 class="text-sm text-zinc-400">Crear cuenta</h2>
            </div>

            <!-- ERRORES -->
            @if ($errors->any())
                <div class="mb-3 text-xs bg-red-900/40 border border-red-700 text-red-300 rounded-lg p-2">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- FORM -->
            <form method="POST" action="{{ route('register') }}" class="space-y-3">
                @csrf

                <!-- NOMBRE -->
                <input type="text" name="name" required
                    placeholder="Nombre completo"
                    value="{{ old('name') }}"
                    class="w-full px-4 py-2.5 text-sm bg-black text-white
                           border border-spikiaPurple/40 rounded-xl
                           placeholder:text-zinc-500
                           focus:outline-none focus:ring-2 focus:ring-neonPink
                           focus:shadow-[0_0_15px_#ff2fa0]
                           transition">

                <!-- EMAIL -->
                <input type="email" name="email" required
                    placeholder="Correo electrónico"
                    value="{{ old('email') }}"
                    class="w-full px-4 py-2.5 text-sm bg-black text-white
                           border border-spikiaPurple/40 rounded-xl
                           placeholder:text-zinc-500
                           focus:outline-none focus:ring-2 focus:ring-neonBlue
                           focus:shadow-[0_0_15px_#4ffcff]
                           transition">

                <!-- PASSWORD -->
                <input type="password" name="password" required
                    placeholder="Contraseña"
                    class="w-full px-4 py-2.5 text-sm bg-black text-white
                           border border-spikiaPurple/40 rounded-xl
                           placeholder:text-zinc-500
                           focus:outline-none focus:ring-2 focus:ring-neonPink
                           focus:shadow-[0_0_15px_#ff2fa0]
                           transition">

                <!-- CONFIRM PASSWORD -->
                <input type="password" name="password_confirmation" required
                    placeholder="Confirmar contraseña"
                    class="w-full px-4 py-2.5 text-sm bg-black text-white
                           border border-spikiaPurple/40 rounded-xl
                           placeholder:text-zinc-500
                           focus:outline-none focus:ring-2 focus:ring-neonPink
                           focus:shadow-[0_0_15px_#ff2fa0]
                           transition">

                <!-- BOTÓN -->
                <button
                  class="w-full py-2.5 mt-2 text-sm font-semibold rounded-xl text-black
                         bg-neonPink shadow-[0_0_25px_#ff2fa0]
                         hover:shadow-[0_0_45px_#ff2fa0]
                         hover:scale-105 transition-all duration-300">
                  Crear cuenta
                </button>
            </form>

            <!-- LOGIN -->
            <p class="text-center text-xs text-zinc-400 mt-4">
                ¿Ya tienes cuenta?
                <a href="{{ route('login') }}"
                   class="text-neonBlue hover:underline">
                    Inicia sesión
                </a>
            </p>

        </div>
    </div>

</body>
</html>
