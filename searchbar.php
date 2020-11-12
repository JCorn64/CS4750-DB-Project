<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="author" content="colorlib.com">
    <link href="https://fonts.googleapis.com/css?family=Poppins" rel="stylesheet" />
    <link href="assets/css/search.css" rel="stylesheet" />
  </head>
  <body>
    <div class="s131">
      <form>
        <div class="inner-form">
          <div class="input-field first-wrap">
            <input id="search" type="text" placeholder="What are you looking for?" />
          </div>
          <div class="input-field second-wrap">
            <div class="input-select">
              <select data-trigger="" name="choices-single-defaul">
                <option placeholder="">CATEGORY</option>
                <option>Subject A</option>
                <option>Subject B</option>
                <option>Subject C</option>
              </select>
            </div>
          </div>
          <div class="input-field third-wrap">
            <button class="btn-search" type="button">SEARCH</button>
          </div>
        </div>
      </form>
    </div>
    <script src="assets/js/extention/choices.js"></script>
    <script>
      const choices = new Choices('[data-trigger]',
      {
        searchEnabled: false
      });

    </script>
  </body>
</html>
