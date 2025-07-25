<?php
/**
 * Financing calculator template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$financing_calc = new Financing_Calculator();
$default_rates = $financing_calc->get_default_rates();
?>

<div class="financing-calculator">
    <div class="calculator-header">
        <h2><?php _e('Car Financing Calculator', 'car-sales-plugin'); ?></h2>
        <p><?php _e('Calculate your monthly payments and get financing advice', 'car-sales-plugin'); ?></p>
    </div>

    <div class="calculator-container">
        <div class="calculator-inputs">
            <form id="financing-form" class="financing-form">
                <div class="form-group">
                    <label for="car-price"><?php _e('Car Price (DKK)', 'car-sales-plugin'); ?> *</label>
                    <input type="number" id="car-price" name="car_price" min="0" step="1000" required>
                </div>

                <div class="form-group">
                    <label for="down-payment"><?php _e('Down Payment (DKK)', 'car-sales-plugin'); ?></label>
                    <input type="number" id="down-payment" name="down_payment" min="0" step="1000" value="0">
                </div>

                <div class="form-group">
                    <label for="loan-term"><?php _e('Loan Term', 'car-sales-plugin'); ?> *</label>
                    <select id="loan-term" name="loan_term" required>
                        <option value=""><?php _e('Select loan term', 'car-sales-plugin'); ?></option>
                        <?php foreach ($default_rates as $months => $rate): ?>
                            <option value="<?php echo esc_attr($months); ?>" data-rate="<?php echo esc_attr($rate); ?>">
                                <?php echo esc_html($months / 12); ?> <?php echo _n('year', 'years', $months / 12, 'car-sales-plugin'); ?> (<?php echo esc_html($rate); ?>%)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="interest-rate"><?php _e('Interest Rate (%)', 'car-sales-plugin'); ?></label>
                    <input type="number" id="interest-rate" name="interest_rate" min="0" max="25" step="0.1" readonly>
                    <small><?php _e('Rate is automatically set based on loan term', 'car-sales-plugin'); ?></small>
                </div>

                <button type="submit" class="calculate-btn">
                    <i class="fas fa-calculator"></i>
                    <?php _e('Calculate', 'car-sales-plugin'); ?>
                </button>
            </form>
        </div>

        <div class="calculator-results" id="results-section" style="display: none;">
            <h3><?php _e('Financing Results', 'car-sales-plugin'); ?></h3>
            <div class="results-grid">
                <div class="result-item">
                    <label><?php _e('Monthly Payment', 'car-sales-plugin'); ?></label>
                    <span id="monthly-payment" class="result-value">0 DKK</span>
                </div>
                <div class="result-item">
                    <label><?php _e('Total Payment', 'car-sales-plugin'); ?></label>
                    <span id="total-payment" class="result-value">0 DKK</span>
                </div>
                <div class="result-item">
                    <label><?php _e('Total Interest', 'car-sales-plugin'); ?></label>
                    <span id="total-interest" class="result-value">0 DKK</span>
                </div>
                <div class="result-item">
                    <label><?php _e('Loan Amount', 'car-sales-plugin'); ?></label>
                    <span id="loan-amount" class="result-value">0 DKK</span>
                </div>
            </div>

            <div class="financing-actions">
                <button type="button" id="book-advice-btn" class="book-advice-btn">
                    <i class="fas fa-calendar-alt"></i>
                    <?php _e('Book Advice', 'car-sales-plugin'); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Advice Booking Modal -->
    <div id="advice-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3><?php _e('Book Financing Advice', 'car-sales-plugin'); ?></h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <p><?php _e('Get personalized financing advice from our experts. Fill out the form below and we will contact you.', 'car-sales-plugin'); ?></p>
                
                <form id="advice-form" class="advice-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="advice-name"><?php _e('Full Name', 'car-sales-plugin'); ?> *</label>
                            <input type="text" id="advice-name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="advice-email"><?php _e('Email Address', 'car-sales-plugin'); ?> *</label>
                            <input type="email" id="advice-email" name="email" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="advice-phone"><?php _e('Phone Number', 'car-sales-plugin'); ?> *</label>
                            <input type="tel" id="advice-phone" name="phone" required>
                        </div>
                        <div class="form-group">
                            <label for="preferred-contact"><?php _e('Preferred Contact Time', 'car-sales-plugin'); ?></label>
                            <select id="preferred-contact" name="preferred_contact">
                                <option value="morning"><?php _e('Morning (9-12)', 'car-sales-plugin'); ?></option>
                                <option value="afternoon"><?php _e('Afternoon (12-17)', 'car-sales-plugin'); ?></option>
                                <option value="evening"><?php _e('Evening (17-20)', 'car-sales-plugin'); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="advice-message"><?php _e('Additional Information', 'car-sales-plugin'); ?></label>
                        <textarea id="advice-message" name="message" rows="4" placeholder="<?php esc_attr_e('Tell us about your financing needs...', 'car-sales-plugin'); ?>"></textarea>
                    </div>

                    <div class="calculation-summary">
                        <h4><?php _e('Your Calculation', 'car-sales-plugin'); ?></h4>
                        <div class="summary-row">
                            <span><?php _e('Car Price:', 'car-sales-plugin'); ?></span>
                            <span id="summary-price">0 DKK</span>
                        </div>
                        <div class="summary-row">
                            <span><?php _e('Monthly Payment:', 'car-sales-plugin'); ?></span>
                            <span id="summary-monthly">0 DKK</span>
                        </div>
                        <div class="summary-row">
                            <span><?php _e('Loan Term:', 'car-sales-plugin'); ?></span>
                            <span id="summary-term">0 years</span>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="submit-advice-btn">
                            <i class="fas fa-paper-plane"></i>
                            <?php _e('Submit Request', 'car-sales-plugin'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
