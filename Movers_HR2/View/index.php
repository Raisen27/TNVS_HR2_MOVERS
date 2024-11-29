

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply Here!</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.lordicon.com/lordicon.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        #hero_section {
            display: flex;
            justify-content: center;
            align-items: center;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            height: 100vh;
            color: #f4f6f8;
            background-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.5), rgba(16, 12, 12, 0.8)), url('../assets/cover.png');
        }

        #Upload_con {
            border: dotted #a6c4e5;
            border-radius: 10px;
            padding: 10px;
        }
    </style>
</head>
<body>
<header class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">
            <img src="../assets/logo.png" alt="logo" height="50">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="#">About Us</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Contact Us</a>
                </li>
                
            </ul>
        </div>
    </div>
</header>

<section id="hero_section">
    <div class="container d-flex flex-column justify-content-center align-items-center">
        <div class="d-flex flex-column justify-content-center align-items-center">
            <h1>Welcome To ABC Taxi Company</h1>
            <p>Be part of Our Family</p>
        </div>
        <div>
            <button data-bs-toggle="modal" data-bs-target="#Modal_apply" type="button" class="btn btn-primary">Apply Now!</button>
        </div>
    </div>
</section>

<!-- Modal -->
<div class="modal fade" id="Modal_apply" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form enctype="multipart/form-data" id="Apply_form" action="../Models/Apply.php" method="post" class="modal-content">
            <div class="modal-body d-flex flex-column gap-3">
                <div class="d-flex gap-3">
                    <div>
                        <label for="Name">
                            <input id="Name" name="Name" type="text" class="form-control" placeholder="Enter Name" required>
                        </label>
                        <div id="Name_msg" class="invalid-feedback"></div>
                    </div>
                    <div>
                        <label for="Age">
                            <input id="Age" name="Age" type="number" class="form-control" placeholder="Age" required>
                        </label>
                        <div id="Age_msg" class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="d-flex gap-3">
                    <div>
                        <label for="Contact_number">
                            <input id="Contact_number" name="Contact_number" type="number" class="form-control" placeholder="Contact Number" required>
                        </label>
                        <div id="Contact_number_msg" class="invalid-feedback"></div>
                    </div>
                    <div>
                        <label for="Email_add">
                            <input id="Email_add" name="Email" type="email" class="form-control" placeholder="Enter Email Address" required>
                        </label>
                        <div id="Email_add_msg" class="invalid-feedback"></div>
                    </div>
                </div>

                <div>
                    <select id="Select_job" name="SelectedJob" class="form-select" aria-label="Default select example" required>
                        <option selected disabled>Select Job Position</option>
                        <?php
                        include '../Models/JobOffer.php';
                        global $conn;
                        $jobName = new JobOffer($conn);
                        $jobName->GeJobName();
                        ?>
                    </select>
                    <div id="Select_job_msg" class="invalid-feedback"></div>
                </div>

                <div>
                    <div id="Upload_con" class="d-flex justify-content-center align-items-center">
                        <label for="upload_resume">
                            <lord-icon
                                src="https://cdn.lordicon.com/fjvfsqea.json"
                                trigger="loop"
                                stroke="dark"
                                style="width:30px;height:30px">
                            </lord-icon>
                            <input accept="application/pdf" type="file" id="upload_resume" name="resume" style="display: none" required>
                            Upload PDF Resume
                        </label>
                    </div>
                    <div id="Resume_msg" class="invalid-feedback"></div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Apply</button>
            </div>
        </form>
    </div>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
<script src="../MainJs/Apply_js.js"></script>


</body>
</html>
