<?php
// Include database connection
include 'session_check.php';
include 'dbconnection.php';

// Fetch statistics for the dashboard
$statistics = array(
    'total_alumni' => 0,
    'employed_alumni' => 0,
    'events_held' => 0,
    'total_donations' => 0
);

$sqlStatistics = [
    "SELECT COUNT(*) AS total FROM Alumni" => 'total_alumni',
    "SELECT COUNT(*) AS total FROM Alumni WHERE EmploymentStatus = 'Employed'" => 'employed_alumni',
    "SELECT COUNT(DISTINCT EventName) AS total FROM Events" => 'events_held', // Count only unique EventNames
    "SELECT SUM(Amount) AS total FROM Donations" => 'total_donations'
];

foreach ($sqlStatistics as $query => $key) {
    $result = $conn->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        $statistics[$key] = $row['total'] ?? 0;
    }
}

// Initialize dataPoints for graduates per year
$graduatesDataPoints = array();
$sqlGraduates = "SELECT GraduationYear, COUNT(*) AS graduates FROM Alumni 
                 WHERE GraduationYear IS NOT NULL
                 GROUP BY GraduationYear 
                 ORDER BY GraduationYear ASC";

$resultGraduates = $conn->query($sqlGraduates);
if ($resultGraduates->num_rows > 0) {
    while ($row = $resultGraduates->fetch_assoc()) {
        $graduatesDataPoints[] = array(
            "y" => (int)$row["graduates"],
            "label" => $row["GraduationYear"]
        );
    }
} else {
    $graduatesDataPoints[] = array(
        "y" => 0,
        "label" => "No Data"
    );
}

// Initialize dataPoints for employment status
$employmentDataPoints = array();
$sqlEmployment = "SELECT EmploymentStatus, COUNT(*) AS count 
                  FROM Alumni 
                  WHERE EmploymentStatus IS NOT NULL 
                  GROUP BY EmploymentStatus";

$resultEmployment = $conn->query($sqlEmployment);
if ($resultEmployment->num_rows > 0) {
    while ($row = $resultEmployment->fetch_assoc()) {
        $employmentDataPoints[] = array(
            "label" => $row["EmploymentStatus"],
            "y" => (int)$row["count"]
        );
    }
} else {
    $employmentDataPoints[] = array(
        "label" => "No Data",
        "y" => 0
    );
}

// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Alumni System Dashboard</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link rel="icon" type="image/png" sizes="32x32" href="storage\image\SLSU.jpeg">
    <link rel="icon" type="image/x-icon" href="storage\image\SLSU.jpeg">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link href="dist/css/AdminLTE.min.css" rel="stylesheet" type="text/css" />
    <link href="dist/css/skins/_all-skins.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
</head>
<body class="skin-blue">
<div class="wrapper">
    <!-- Main Header -->
    <header class="main-header">
        <a href="#" class="logo"><b>MSIT Alumni</b>System</a>
        <nav class="navbar navbar-static-top" role="navigation">
            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">
                    <li class="dropdown user user-menu">
                        <a href="adminprofile.php" class="dropdown-toggle" data-toggle="dropdown">
                            <img src="storage\image\SLSU.jpeg" class="user-image" alt="User Image" />
                            <span class="hidden-xs">Admin</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="user-header">
                                <img src="dist/img/user2-160x160.jpg" class="img-circle" alt="User Image" />
                                <p>
                                    Admin - Alumni System
                                    <small>Since Jan. 2025</small>
                                </p>
                            </li>
                            <li class="user-footer">
                                <div class="pull-left">
                                    <a href="adminprofile.php" class="btn btn-default btn-flat">Profile</a>
                                </div>
                                <div class="pull-right">
                                    <a href="logout.php" class="btn btn-default btn-flat">Sign out</a>
                                </div>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <!-- Sidebar -->
    <aside class="main-sidebar">
        <section class="sidebar">
            <ul class="sidebar-menu">
                <li class="header">MAIN NAVIGATION</li>
                <li><a href="#"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>
                <li><a href="alumni.php"><i class="fa fa-users"></i> <span>Alumni Directory</span></a></li>
                <li><a href="employment.php"><i class="fa fa-briefcase"></i> <span>Employment</span></a></li>
                <li><a href="employmenthistory.php"><i class="fa fa-briefcase"></i> <span>Employment History</span></a></li>
                <li><a href="events.php"><i class="fa fa-calendar"></i> <span>Events</span></a></li>
                <li><a href="donations.php"><i class="fa fa-gift"></i> <span>Donations</span></a></li>
                <li><a href="reports.php"><i class="fa fa-bar-chart"></i> <span>Reports</span></a></li>
                <li><a href="logout.php"><i class="fa fa-sign-out"></i> <span>Logout</span></a></li>
            </ul>
        </section>
    </aside>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Dashboard
                <small>Welcome to the Alumni System</small>
            </h1>
        </section>

        <!-- Main Content -->
        <section class="content">
            <div class="row">
                <!-- Alumni Statistics -->
                <div class="col-lg-3 col-xs-6">
                    <div class="small-box bg-aqua">
                        <div class="inner">
                            <h3><?= $statistics['total_alumni'] ?></h3>
                            <p>Total Alumni</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-users"></i>
                        </div>
                        <a href="alumni.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-xs-6">
                    <div class="small-box bg-green">
                        <div class="inner">
                            <h3><?= $statistics['employed_alumni'] ?></h3>
                            <p>Employed Alumni</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-briefcase"></i>
                        </div>
                        <a href="employment.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-xs-6">
                    <div class="small-box bg-yellow">
                        <div class="inner">
                            <h3><?= $statistics['events_held'] ?></h3>
                            <p>Events Held</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-calendar"></i>
                        </div>
                        <a href="events.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-xs-6">
                    <div class="small-box bg-red">
                        <div class="inner">
                            <h3><?= number_format($statistics['total_donations'], 2) ?></h3>
                            <p>Total Donations</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-gift"></i>
                        </div>
                        <a href="donations.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>

            <!-- Report Section -->
            <div class="row">
                <!-- Bar Chart for Graduates Per Year -->
                <div class="col-md-6">
                    <div class="box box-info">
                        <div class="box-header with-border">
                            <h3 class="box-title">Graduates Per Year</h3>
                        </div>
                        <div class="box-body">
                            <div id="graduatesChartContainer" style="height: 370px; width: 100%;"></div>
                        </div>
                    </div>
                </div>
                <!-- Pie Chart for Employment Status -->
                <div class="col-md-6">
                    <div class="box box-success">
                        <div class="box-header with-border">
                            <h3 class="box-title">Employment Status</h3>
                        </div>
                        <div class="box-body">
                            <div id="employmentChartContainer" style="height: 370px; width: 100%;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <strong>&copy; 2025 Alumni System.</strong> All rights reserved.
    </footer>
</div>

<!-- Scripts -->
<script>
    window.onload = function () {
        // Bar Chart for Graduates Per Year
        var graduatesChart = new CanvasJS.Chart("graduatesChartContainer", {
            animationEnabled: true,
            theme: "light2",
            title: {
                text: "Number of Graduates Per Year"
            },
            axisY: {
                title: "Number of Graduates"
            },
            data: [{
                type: "column",
                yValueFormatString: "#,##0",
                dataPoints: <?= json_encode($graduatesDataPoints, JSON_NUMERIC_CHECK); ?>
            }]
        });
        graduatesChart.render();

        // Pie Chart for Employment Status
        var employmentChart = new CanvasJS.Chart("employmentChartContainer", {
            animationEnabled: true,
            title: {
                text: "Employment Status"
            },
            data: [{
                type: "pie",
                yValueFormatString: "#,##0",
                indexLabel: "{label} ({y})",
                dataPoints: <?= json_encode($employmentDataPoints, JSON_NUMERIC_CHECK); ?>
            }]
        });
        employmentChart.render();
    };
</script>
</body>
</html>
