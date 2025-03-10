<?php
$pageTitle = "Frequently Asked Questions";
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get FAQ content from settings
$faq_intro = getSetting('faq_intro', 'Find answers to commonly asked questions about our products and services.');

include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="mb-4">Frequently Asked Questions</h1>
                    
                    <div class="mb-4">
                        <p><?php echo htmlspecialchars($faq_intro); ?></p>
                    </div>
                    
                    <div class="accordion" id="faqAccordion">
                        <?php
                        // Get all FAQ questions and answers from settings
                        $faq_count = 0;
                        
                        // First check if we have dedicated FAQ content
                        $faq_content = getSetting('faq_content');
                        if (!empty($faq_content)) {
                            echo $faq_content;
                        } else {
                            // Otherwise, use the individual FAQ items from settings
                            for ($i = 1; $i <= 10; $i++) {
                                $question = getSetting('faq_question_' . $i);
                                $answer = getSetting('faq_answer_' . $i);
                                
                                if (!empty($question) && !empty($answer)) {
                                    $faq_count++;
                                    ?>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading<?php echo $i; ?>">
                                            <button class="accordion-button <?php echo ($i > 1) ? 'collapsed' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $i; ?>" aria-expanded="<?php echo ($i === 1) ? 'true' : 'false'; ?>" aria-controls="collapse<?php echo $i; ?>">
                                                <?php echo htmlspecialchars($question); ?>
                                            </button>
                                        </h2>
                                        <div id="collapse<?php echo $i; ?>" class="accordion-collapse collapse <?php echo ($i === 1) ? 'show' : ''; ?>" aria-labelledby="heading<?php echo $i; ?>" data-bs-parent="#faqAccordion">
                                            <div class="accordion-body">
                                                <?php echo $answer; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                            }
                            
                            if ($faq_count === 0) {
                                echo '<div class="alert alert-info">No FAQ items have been set up yet.</div>';
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 