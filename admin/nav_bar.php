<?php ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style type="text/css">
        :root{
  --color-1: #0f1016;
  --text-color: #f0f0f0;
  --accent-color: #006aff;
}
*{
  margin: 0;
  padding: 0;
}
html{
  font-size: 12pt;
}
nav{
  height: 60px;
  background-color: var(--color-1);
  display: flex;
  justify-content: flex-end;
  align-items: center;
}
.links-container{
  height: 100%;
  width: 100%;
  display: flex;
  flex-direction: row;
  align-items: center;
}
nav a{
  height: 100%;
  padding: 0 20px;
  display: flex;
  align-items: center;
  text-decoration: none;
  color: var(--text-color);
}
nav a:hover{
  background-color: var(--accent-color);
}
nav .home-link{
  margin-right: auto;
}
nav svg{
  fill: var(--text-color);
}
#sidebar-active{
  display: none;
}
.open-sidebar-button, .close-sidebar-button{
  display: none;
}
@media(max-width: 575px){
  .links-container{
    flex-direction: column;
    align-items: flex-start;

    position: fixed;
    top: 0;
    right: -100%;
    z-index: 10;
    width: 300px;

    background-color: var(--color-1);
    box-shadow: -5px 0 5px rgba(0, 0, 0, 0.25);
    transition: 0.75s ease-out;
  }
  nav a{
    box-sizing: border-box;
    height: auto;
    width: 100%;
    padding: 20px 30px;
    justify-content: flex-start;
  }
  .open-sidebar-button, .close-sidebar-button{
    padding: 20px;
    display: block;
  }
  #sidebar-active:checked ~ .links-container{
    right: 0;
  }
  #sidebar-active:checked ~ #overlay{
    height: 100%;
    width: 100%;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 9;
  }
}
    </style>
    <title>Navbar</title>
</head>
<body>
    <nav>
        <input type="checkbox" id="sidebar-active">
        <label for="sidebar-active" class="open-sidebar-button">
            <svg xmlns="http://www.w3.org/2000/svg" height="32px" viewBox="0 -960 960 960" width="32px" fill="#e8eaed"><path d="M120-240v-80h720v80H120Zm0-200v-80h720v80H120Zm0-200v-80h720v80H120Z"/></svg>
        </label>

        <label for="sidebar-active" id="overlay"></label>
        <div class="links-container">
            <label for="sidebar-active" class="close-sidebar-button">
                <svg xmlns="http://www.w3.org/2000/svg" height="32" viewBox="0 -960 960 960" width="32" fill="#e8eaed"><path d="m256-200-56-56 224-224-224-224 56-56 224 224 224-224 56 56-224 224 224 224-56 56-224-224-224 224Z"/></svg>
            </label>

            <a href="admin_dashboard.php">Admin Dashboard</a>
            <a href="cars.php">Car Management</a>
            <a href="users.php">User Management</a>
            <a href="bookings.php">Bookings</a>
            <a href="edit_profile.php">Edit Profile</a>

        </div>
    </nav>
</body>
</html>