<!DOCTYPE html>
<html>
  <head>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.11.2/css/all.css" integrity="sha384-KA6wR/X5RY4zFAHpv/CnoG2UW1uogYfdnP67Uv7eULvTveboZJg0qUpmJZb5VqzN" crossorigin="anonymous">
    <link rel="stylesheet" href="css/style.css">
  </head>
  <body id="page-login"class="bg-dots">
    <header>
      <div>
        <img class="siteLogo" src="img/logo.png">
      </div>
    </header>
    <main>
      <div class="container">
        <form>
          <div class="form-top">
            <div class="content-center">
              <h2>Welcome!</h2>
              <p class="form-helper"><a>New Username</a></p>
              <div class="form-input-group d-flex flex-row">
                <label for="school_username">School Username</label>
                <input id="school_username" class="cprInput" type="text" name="school_username" placeholder="SCHOOL USERNAME">
                <i class="fas fa-user form-icon-right"></i>
              </div>
              <p class="form-helper"><a>Forgot Your Password?</a></p>
              <div class="form-input-group d-flex flex-row">
                <label for="school_password">School Password</label>
                <input id="school_password" class="cprInput" type="text" name="school_password" placeholder="SCHOOL PASSWORD">
                <i class="fas fa-key form-icon-right"></i>
              </div>
            </div>
          </div>
          <div class="form-bottom">
            <div class="content-center">
              <div class="d-flex flex-row align-item-center">
                <label class="fItem2" for="school_password">Keep Me Signed In</label>
                <input id="retain_login" class="fItem1" type="checkbox" name="retain_login">
                <input id="submit_login" class="fItem3 bg-red" type="submit" name="submit_login" value="Sign In">
              </div>
            </div>
          </div>
        </form>
      </div>
    </main>
    <footer>
    </footer>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
  </body>
</html>
