<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Restaurant Menu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .menu-item {
            border-bottom: 1px solid #ddd;
            padding: 15px 0;
        }
        .menu-item:last-child {
            border-bottom: none;
        }
        .price {
            color: #28a745;
            font-weight: bold;
        }
        .description {
            color: #6c757d;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Our Menu</h1>
            <a href="index.php" class="btn btn-primary btn-lg">Make a Reservation</a>
        </div>

        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="menu-item">
                            <h4>Grilled Salmon</h4>
                            <p class="description">Fresh Atlantic salmon with lemon butter sauce and seasonal vegetables</p>
                            <p class="price">$24.99</p>
                        </div>

                        <div class="menu-item">
                            <h4>Filet Mignon</h4>
                            <p class="description">8oz premium cut beef tenderloin with garlic mashed potatoes</p>
                            <p class="price">$32.99</p>
                        </div>

                        <div class="menu-item">
                            <h4>Mushroom Risotto</h4>
                            <p class="description">Creamy Arborio rice with wild mushrooms and parmesan</p>
                            <p class="price">$18.99</p>
                        </div>

                        <div class="menu-item">
                            <h4>Chicken Marsala</h4>
                            <p class="description">Pan-seared chicken breast in Marsala wine sauce with mushrooms</p>
                            <p class="price">$21.99</p>
                        </div>

                        <div class="menu-item">
                            <h4>Seafood Pasta</h4>
                            <p class="description">Linguine with shrimp, scallops, and mussels in white wine sauce</p>
                            <p class="price">$26.99</p>
                        </div>

                        <div class="menu-item">
                            <h4>Vegetable Stir-Fry</h4>
                            <p class="description">Fresh seasonal vegetables with tofu in ginger soy sauce</p>
                            <p class="price">$16.99</p>
                        </div>

                        <div class="menu-item">
                            <h4>Rack of Lamb</h4>
                            <p class="description">Herb-crusted lamb rack with mint sauce and roasted potatoes</p>
                            <p class="price">$34.99</p>
                        </div>

                        <div class="menu-item">
                            <h4>Duck Confit</h4>
                            <p class="description">Slow-cooked duck leg with cherry sauce and wild rice</p>
                            <p class="price">$28.99</p>
                        </div>

                        <div class="menu-item">
                            <h4>Lobster Thermidor</h4>
                            <p class="description">Baked lobster with brandy cream sauce and gruyere cheese</p>
                            <p class="price">$39.99</p>
                        </div>

                        <div class="menu-item">
                            <h4>Truffle Pasta</h4>
                            <p class="description">Handmade fettuccine with black truffle and cream sauce</p>
                            <p class="price">$25.99</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
