<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Spikia')</title>
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
                glow: 'glow 2s ease-in-out infinite alternate',
                spinSlow: 'spin 8s linear infinite',
              },
              keyframes: {
                glow: {
                  '0%': { boxShadow: '0 0 15px #7C3AED' },
                  '50%': { boxShadow: '0 0 35px #ff2fa0' },
                  '100%': { boxShadow: '0 0 55px #4ffcff' },
                }
              }
            }
          }
        }
    </script>
</head>

<body class="min-h-screen bg-gradient-to-br from-black via-zinc-900 to-black text-white">

    <!-- CONTENIDO -->
    @yield('content')

</body>
</html>
