<!DOCTYPE html>
<html>
<head>
    <title>Login | Smart Warehouse</title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body style="background-color:#F1F5F9;">

<div class="min-vh-100 d-flex justify-content-center align-items-center">

    <div class="card shadow-sm p-4"
         style="width:360px; border-radius:18px;">

        <h4 class="text-center mb-4"
            style="color:#166534; font-weight:600;">
            SMART WAREHOUSE<br>
            MONITORING
        </h4>

        @if(session('error'))
            <div class="alert alert-danger py-2">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-3">
                <input type="text"
                       name="username"
                       class="form-control"
                       placeholder="Username"
                       required>
            </div>

            <div class="mb-4">
                <input type="password"
                       name="password"
                       class="form-control"
                       placeholder="Password"
                       required>
            </div>

            <button type="submit"
                    class="btn w-100"
                    style="
                        background-color:#16A34A;
                        color:white;
                        border-radius:12px;
                        padding:10px;
                        font-weight:500;
                    ">
                Login
            </button>
        </form>

    </div>
</div>

</body>
</html>
