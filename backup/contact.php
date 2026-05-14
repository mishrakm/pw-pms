<?php
// Pluswealth Contact Page
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
                            <h2 class="text-white wow fadeInDown" data-wow-delay=".2s">Get In Touch</h2>
                            <p class="text-white wow fadeInLeft"  data-wow-delay=".4s">Schedule a conversation with our portfolio management team</p>
                            <a href="#contact" class="theme-btn wow fadeInUp"  data-wow-delay=".6s">Contact Us</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- ========================= hero-section end ========================= -->

        <!-- ========================= contact-section start ========================= -->
        <section id="contact" class="contact-section pt-120 pb-105">
            <div class="container">
                <div class="row align-items-end">
                    <div class="col-xl-12 col-lg-12">
                        <div class="contact-wrapper mb-30">
                            <h2 class="mb-20 wow fadeInDown" data-wow-delay=".2s">Connect With Us</h2>
                            <p class="mb-55 wow fadeInUp" data-wow-delay=".4s">Schedule a Call with the Portfolio Manager</p>
                            <form action="assets/mail.php" method="POST" id="contact-form" class="contact-form">
                                <div class="row">
                                    <div class="col-lg-6 col-md-6">
                                        <input type="text" id="name" name="name" placeholder="Name">
                                    </div>
                                    <div class="col-lg-6 col-md-6">
                                        <input type="email" id="email" name="email" placeholder="Email">
                                    </div>
                                    <div class="col-lg-6 col-md-6">
                                        <input type="text" id="phone" name="phone" placeholder="Phone">
                                    </div>
                                    <div class="col-lg-6 col-md-6">
                                        <input type="text" id="subject" name="subject" placeholder="City">
                                    </div>
                                    <div class="col-lg-12">
                                        <input type="text" id="subject" name="ticket_size" placeholder="Ticket Size">
                                    </div>
                                </div>                                
                                <button type="submit" class="theme-btn theme-btn-2">SEND MESSAGE</button>
                            </form>
                        </div>
                    </div>
                    
                </div>
            </div>
        </section>
        <!-- ========================= contact-section end ========================= -->

<?php
include 'includes/footer.php';
?>
