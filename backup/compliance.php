<?php
// Pluswealth Compliance Page
include 'includes/header.php';
?>

        <!-- ========================= hero-section start ========================= -->
        <section id="home" class="hero-section" style="background-image: url('assets/img/money-coins-tree-growing-jar_50039-1102.jpg');">
            <div class="shapes">
                <div class="shape shape-1"></div>
                <div class="shape shape-2"></div>
                <div class="shape shape-3"></div>
            </div>
            <div class="hero-overlay"></div>
            <div class="container hero-container">
                <div class="row align-items-center h-100">
                    <div class="col-lg-8 mx-auto">
                        <div class="hero-content-wrapper text-center">
                            <h2 class="text-white wow fadeInDown" data-wow-delay=".2s">Compliance</h2>
                            <p class="text-white wow fadeInLeft"  data-wow-delay=".4s">Portfolio Manager Complaint Data & Regulatory Information</p>
                            <a href="#compliance" class="theme-btn wow fadeInUp"  data-wow-delay=".6s">View Details</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- ========================= hero-section end ========================= -->

        <!-- ========================= compliance-section start ========================= -->
        <section id="compliance" class="compliance-section pt-120 pb-120">
            <div class="container">
                <div class="row">
                    <div class="col-xl-10 col-lg-10 mx-auto">
                        <div class="section-title text-center mb-55">
                            <h2 class="mb-20 wow fadeInUp" data-wow-delay=".2s">Complaint Data</h2>
                            <p class="wow fadeInUp" data-wow-delay=".4s">Complaint Data to be displayed by the Portfolio Managers</p>
                        </div>
                    </div>
                </div>

                <!-- Data for the month ending Oct 31, 2025 -->
                <div class="row mb-60">
                    <div class="col-xl-12 col-lg-12 mx-auto">
                        <h3 class="mb-30 wow fadeInUp" data-wow-delay=".2s">Data for the month ending Apr 30, 2026</h3>
                        <div class="table-responsive mb-30 wow fadeInUp" data-wow-delay=".4s">
                            <table class="table table-striped table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Sr. No.</th>
                                        <th>Received From</th>
                                        <th>Pending at the end of last month</th>
                                        <th>Received</th>
                                        <th>Resolved^</th>
                                        <th>Total Pending #</th>
                                        <th>Pending complaints > 3 months</th>
                                        <th>Average resolution time in days ^</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>Directly from Investors</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0</td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>SEBI (SCORES)</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0</td>
                                    </tr>
                                    <tr>
                                        <td>3</td>
                                        <td>Other Sources (if any)</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0</td>
                                    </tr>
                                    <tr class="table-dark">
                                        <td><strong>Grand Total</strong></td>
                                        <td></td>
                                        <td><strong>0</strong></td>
                                        <td><strong>0</strong></td>
                                        <td><strong>0</strong></td>
                                        <td><strong>0</strong></td>
                                        <td><strong>0</strong></td>
                                        <td><strong>0</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="text-muted small mb-40">^ Average Resolution time is the sum total of time taken to resolve each complaint in days, in the current month divided by total number of complaints resolved in the current month.</p>
                    </div>
                </div>

                <!-- Trend of monthly disposal of complaint -->
                <div class="row mb-60">
                    <div class="col-xl-12 col-lg-12 mx-auto">
                        <h3 class="mb-30 wow fadeInUp" data-wow-delay=".2s">Trend of monthly disposal of complaint</h3>
                        <div class="table-responsive mb-30 wow fadeInUp" data-wow-delay=".4s">
                            <table class="table table-striped table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Sr. No.</th>
                                        <th>Month</th>
                                        <th>Carried forward from previous month</th>
                                        <th>Received</th>
                                        <th>Resolved</th>
                                        <th>Pending #</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>April 2026</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0</td>
                                    </tr>
                                    <tr class="table-dark">
                                        <td colspan="2"><strong>Grand Total</strong></td>
                                        <td><strong>0</strong></td>
                                        <td><strong>0</strong></td>
                                        <td><strong>0</strong></td>
                                        <td><strong>0</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="text-muted small mb-40">*Inclusive of complaints of previous months resolved in the current month. #Inclusive of complaints pending as on the last day of the month</p>
                    </div>
                </div>

                <!-- Trend of annual disposal of complaints -->
                <div class="row">
                    <div class="col-xl-12 col-lg-12 mx-auto">
                        <h3 class="mb-30 wow fadeInUp" data-wow-delay=".2s">Trend of annual disposal of complaints</h3>
                        <div class="table-responsive mb-30 wow fadeInUp" data-wow-delay=".4s">
                            <table class="table table-striped table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>SN</th>
                                        <th>Year</th>
                                        <th>Carried forward from previous year</th>
                                        <th>Received</th>
                                        <th>Resolved**</th>
                                        <th>Pending##</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>2023-2024</td>
                                        <td>Not Applicable</td>
                                        <td>Not Applicable</td>
                                        <td>Not Applicable</td>
                                        <td>Not Applicable</td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>2024-2025</td>
                                        <td>Not Applicable</td>
                                        <td>Not Applicable</td>
                                        <td>Not Applicable</td>
                                        <td>Not Applicable</td>
                                    </tr>
                                    <tr>
                                        <td>3</td>
                                        <td>2025-2026</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0</td>
                                    </tr>
                                    <tr>
                                        <td>4</td>
                                        <td>2026-2027</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0</td>
                                    </tr>
                                    <tr class="table-dark">
                                        <td colspan="2"><strong>Grand Total</strong></td>
                                        <td><strong>0</strong></td>
                                        <td><strong>0</strong></td>
                                        <td><strong>0</strong></td>
                                        <td><strong>0</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="text-muted small">** Inclusive of complaints of previous years resolved in the current year. ## Inclusive of complaints pending as on last day of the year.</p>
                    </div>
                </div>
            </div>
        </section>
        <!-- ========================= compliance-section end ========================= -->

<?php
include 'includes/footer.php';
?>
