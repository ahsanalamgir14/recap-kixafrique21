<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="https://fonts.googleapis.com/css?family=Roboto:400,100,300,700" rel="stylesheet" type="text/css">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
  <section class="ftco-section">
    <nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light" id="ftco-navbar">
      <div class="container">
        <button class="navbar-toggler menu-button" type="button" data-toggle="collapse" data-target="#ftco-nav" aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <button class="navbar-toggler close-button" type="button" aria-label="Close navigation">
          <span class="fa fa-times"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-center" id="ftco-nav">
          <ul class="navbar-nav">
            <li class="nav-item"><a href="https://kixafrique21.org" class="nav-link">ACCUEIL</a></li>
            <li class="nav-item"><a href="https://agora.kixafrique21.org" class="nav-link">KIX AGORA</a></li>
            <li class="nav-item"><a href="https://carto.kixafrique21.org" class="nav-link">KIX CARTO</a></li>
            <li class="nav-item active"><a href="https://recap.kixafrique21.org" class="nav-link">KIX RECAP</a></li>
          </ul>
        </div>
      </div>
    </nav>
  </section>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
  <script>
    $(document).ready(function () {
      $('.menu-button').click(function () {
        $('#ftco-nav').collapse('toggle');
        $(this).hide();
        $('.close-button').show();
      });

      $('.close-button').click(function () {
        $('#ftco-nav').collapse('hide');
        $(this).hide();
        $('.menu-button').show();
      });

      $('#ftco-nav .nav-link').click(function () {
        $('#ftco-nav').collapse('hide');
        $('.close-button').hide();
        $('.menu-button').show();
      });

      // Initial state
      $('.close-button').hide();
    });
  </script>

  <script src="assets/js/jquery.min.js"></script>
  <script src="assets/js/popper.js"></script>
  <script src="assets/js/bootstrap.min.js"></script>
  <script src="assets/js/main.js"></script>
</body>