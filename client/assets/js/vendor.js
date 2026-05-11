/* 1. UTILITY & TOAST LOGIC */
function showToast(message) {
    const toast = document.getElementById("toast-container");
    if (!toast) {
        console.error("Toast container not found in HTML!");
        return;
    }
    toast.innerHTML = message;
    toast.style.display = "block";

    // Hide it after 3 seconds
    setTimeout(() => {
        toast.style.display = "none";
    }, 3000);
}

function highlightMissing(groups) {
    groups.forEach(name => {
        const input = document.querySelector(`input[name="${name}"]`);
        if (input) {
            const container = input.closest('.compliance-row, .agreement-card');
            if (container) {
                container.style.border = "1px solid #ff4d4d";
                container.style.backgroundColor = "#fff5f5";

                // Reset after 3 seconds
                setTimeout(() => {
                    container.style.border = "";
                    container.style.backgroundColor = "";
                }, 3000);
            }
        }
    });
}

/* 2. GLOBAL NAVIGATION & STEP LOGIC */
window.nextStep = function(stepNumber) {
    const currentStep = document.querySelector('.step-container.active');
    if (!currentStep) return;

    const currentStepNum = parseInt(currentStep.id.split('-')[1]);
    const isGoingForward = stepNumber > currentStepNum;

    if (isGoingForward) {
        /* --- STEP 1 VALIDATION --- */
        if (currentStep.id === "step-1") {
            const requiredInputs = currentStep.querySelectorAll('input[required], select[required]');
            let allInputsValid = true;
            requiredInputs.forEach(input => {
                if (!input.value.trim()) {
                    allInputsValid = false;
                    input.style.borderColor = "#ff4d4d";
                } else {
                    input.style.borderColor = "#D9D9D9";
                }
            });

            const categorySelect = document.getElementById('product-category');
            const selectedCategory = categorySelect.value.trim();
            let checkboxValid = false;
            if (selectedCategory === "Food Products") {
                if (document.querySelectorAll('input[name="food"]:checked').length > 0) checkboxValid = true;
            } else if (selectedCategory === "Non-food Products") {
                if (document.querySelectorAll('input[name="non-food"]:checked').length > 0) checkboxValid = true;
            }

            if (!allInputsValid || !checkboxValid) {
                showToast(!allInputsValid ? "Please fill out all required fields." : "Please select at least one Product Type.");
                return;
            }
        }

        /* --- STEP 2 VALIDATION --- */
        if (currentStep.id === "step-2") {
            const radioInputs = currentStep.querySelectorAll('input[type="radio"]');
            const groups = [...new Set([...radioInputs].map(r => r.name))];
            let allAnswered = true;

            groups.forEach(groupName => {
                const checkedOption = currentStep.querySelector(`input[name="${groupName}"]:checked`);
                const inputElement = currentStep.querySelector(`input[name="${groupName}"]`);
                const container = inputElement ? inputElement.closest('.compliance-row, .agreement-card') : null;

                if (!checkedOption) {
                    allAnswered = false;
                    if (container) container.style.border = "2px solid #ff4d4d";
                } else {
                    if (container) container.style.border = "";
                }
            });

            if (!allAnswered) {
                showToast("Please answer all requirements and compliance terms.");
                return;
            }

            const brgyClearance = currentStep.querySelector('input[name="req"]:checked').value;
            const legalId = currentStep.querySelector('input[name="req1"]:checked').value;

            if (brgyClearance === "no" && legalId === "no") {
                showToast("You must have at least one of the needed requirements to proceed.");
                const reqRows = currentStep.querySelectorAll('.compliance-row');
                reqRows.forEach(row => {
                    row.style.border = "2px solid #ff4d4d";
                    row.style.backgroundColor = "#fff5f5";
                });
                return;
            }
        }

        /* --- STEP 3 VALIDATION --- */
        if (currentStep.id === "step-3") {
            const inputs = currentStep.querySelectorAll('input[type="text"], input[type="time"], input[type="number"]');
            let step3Valid = true;

            inputs.forEach(input => {
                if (input.id === "other-mode-input") {
                    const container = document.getElementById('other-mode-container');
                    if (container && container.classList.contains('active') && !input.value.trim()) {
                        step3Valid = false;
                        input.style.borderColor = "#ff4d4d";
                    } else {
                        input.style.borderColor = "#D9D9D9";
                    }
                } else if (!input.value.trim()) {
                    step3Valid = false;
                    input.style.borderColor = "#ff4d4d";
                } else {
                    input.style.borderColor = "#D9D9D9";
                }
            });

            if (!step3Valid) {
                showToast("Please provide all business details and specify your mode of selling.");
                return;
            }
        }
    }

    /* --- STEP 2 TRANSITION SPECIFIC (REQ OVERRIDE) --- */
    if (stepNumber === 3) {
        const step2Container = document.getElementById('step-2');
        const radioInputs = step2Container.querySelectorAll('input[type="radio"]');
        const radioGroups = [...new Set([...radioInputs].map(r => r.name))];
        let missingFields = [];

        radioGroups.forEach(groupName => {
            const isChecked = step2Container.querySelector(`input[name="${groupName}"]:checked`);
            if (!isChecked) missingFields.push(groupName);
        });

        if (missingFields.length > 0) {
            showToast("Please answer all compliance questions and agree to the terms.");
            highlightMissing(missingFields);
            return;
        }
    }

    /* --- NAVIGATION EXECUTION --- */
    const targetStep = document.getElementById('step-' + stepNumber);
    if (targetStep) {
        document.querySelectorAll('.step-container').forEach(c => c.classList.remove('active'));
        targetStep.classList.add('active');

        document.querySelectorAll('.step').forEach((step, index) => {
            if (index + 1 <= stepNumber) {
                step.classList.add('active');
            } else {
                step.classList.remove('active');
            }
        });

        window.scrollTo({ top: 0, behavior: 'smooth' });
    } else {
        console.error("Target step not found: step-" + stepNumber);
    }
};

/* 3. INITIALIZATION & EVENT LISTENERS */
document.addEventListener("DOMContentLoaded", function () {
    // Step 1: Category Toggles
    const categorySelect = document.querySelector('select');
    const foodSection = document.getElementById("food-section");
    const nonFoodSection = document.getElementById("non-food-section");

    if (categorySelect && foodSection && nonFoodSection) {
        function toggleProductSections() {
            const value = categorySelect.value;
            if (value === "Food Products") {
                foodSection.style.display = "block";
                nonFoodSection.style.display = "none";
            } else if (value === "Non-food Products") {
                foodSection.style.display = "none";
                nonFoodSection.style.display = "block";
            }
        }
        categorySelect.addEventListener("change", toggleProductSections);
        toggleProductSections();
    }

    // Step 1: "Other" Triggers
    const otherTriggers = document.querySelectorAll('.other-trigger');
    otherTriggers.forEach(trigger => {
        trigger.addEventListener('change', function() {
            let targetId = "";
            if (this.id === 'food-other') targetId = 'food-other-input';
            else if (this.id === 'non-food-other') targetId = 'non-food-other-input';

            if (!targetId) return;

            const targetBox = document.getElementById(targetId);
            if (targetBox) {
                const input = targetBox.querySelector('input');
                if (this.checked) {
                    targetBox.style.display = "block";
                    if (input) input.setAttribute('required', 'true');
                } else {
                    targetBox.style.display = "none";
                    if (input) {
                        input.removeAttribute('required');
                        input.value = "";
                    }
                }
            }
        });
    });

    // Step 4: File Upload Labels
    document.querySelectorAll('input[type="file"]').forEach(input => {
        input.addEventListener('change', function() {
            const fileName = this.files.length > 0 ? this.files[0].name : "Choose File";
            const label = this.nextElementSibling;
            if (label && label.querySelector('.file-name')) {
                label.querySelector('.file-name').textContent = fileName;
                if (this.files.length > 0) {
                    label.style.borderColor = "#28a745";
                    label.style.color = "#28a745";
                }
            }
        });
    });

    // Step 5: Form Submission
    const regForm = document.getElementById('registrationForm');
    if (regForm) {
        regForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('business_name', document.getElementById('business-name').value);
            formData.append('owner_name', document.getElementById('vendor-name').value);
            formData.append('business_type', document.getElementById('product-category').value);
            formData.append('contact_number', document.getElementById('contact-number').value);
            formData.append('address', document.getElementById('home-address').value);
            
            const idFront = document.getElementById('valid-id-front').files[0];
            const idBack = document.getElementById('valid-id-back').files[0];
            const brgyClr = document.getElementById('brgy-clr').files[0];
            if (idFront) formData.append('valid_id_front', idFront);
            if (idBack) formData.append('valid_id_back', idBack);
            if (brgyClr) formData.append('barangay_clearance', brgyClr);
            
            // Add validation before form submission
            if (!validateVendorForm()) {
                return;
            }

            try {
                const response = await fetch('../server/actions/submit_vendor.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if(result.success) {
                    document.getElementById('step-5').classList.remove('active');
                    const step6 = document.getElementById('step-6');
                    if (step6) step6.classList.add('active');
                    const p6 = document.getElementById('p-6');
                    if(p6) p6.classList.add('active');
                    
                    // Reload page to show pending status
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showToast('Failed to submit registration: ' + result.error);
                }
            } catch(error) {
                showToast('An error occurred during submission.');
            }
        });
    }
});

/* 4. STEP-SPECIFIC FUNCTIONS */

// Step 2: Rules Links
function openRules(type) {
    let url = "";
    if (type === 'sanitation') url = "sanitation-rules.html";
    else if (type === 'product') url = "product-rules.html";
    if (url) window.open(url, '_blank');
}

// Step 3: Mode Toggle
function toggleOtherMode(selectElement) {
    const otherContainer = document.getElementById('other-mode-container');
    const otherInput = document.getElementById('other-mode-input');

    if (selectElement.value === 'Other') {
        otherContainer.classList.add('active');
        otherContainer.classList.remove('hidden');
        if (otherInput) otherInput.setAttribute('required', 'true');
    } else {
        otherContainer.classList.add('hidden');
        otherContainer.classList.remove('active');
        if (otherInput) {
            otherInput.removeAttribute('required');
            otherInput.value = "";
        }
    }
}

// Step 5: Certification Logic
function toggleSubmitBtn(checkbox) {
    const submitBtn = document.getElementById('submit-reg');
    if (checkbox.checked) {
        submitBtn.disabled = false;
        submitBtn.style.opacity = "1";
        submitBtn.style.cursor = "pointer";
    } else {
        submitBtn.disabled = true;
        submitBtn.style.opacity = "0.5";
        submitBtn.style.cursor = "not-allowed";
    }
}

/* 5. PDF GENERATION */
window.generatePermitPDF = async function(event) {
    let btn = null;
    if (event && event.currentTarget) {
        btn = event.currentTarget;
    } else {
        btn = document.getElementById('downloadPermitApproved');
    }

    if (btn && btn.disabled) return;

    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    let vendorName = "FRIEZA BATUNGBAKAL";
    let productType = "STREET FOOD VENDOR";

    if (btn && btn.dataset.owner) {
        vendorName = btn.dataset.owner;
        productType = btn.dataset.type;
    } else {
        vendorName = document.getElementById('vendor-name')?.value || "FRIEZA BATUNGBAKAL";
        productType = document.getElementById('product-category')?.value || "STREET FOOD VENDOR";
    }
    const year = 2026;

    const getImageData = (url) => {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.onload = () => {
                const canvas = document.createElement('canvas');
                canvas.width = img.width;
                canvas.height = img.height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0);
                resolve(canvas.toDataURL('image/png'));
            };
            img.onerror = () => reject(`Failed to load image at ${url}`);
            img.src = url;
        });
    };

    try {
        const sealData = await getImageData('assets/img/MANILA_SEAL.png');
        const kanjiData = await getImageData('assets/img/KANJI_LOGO.png');

        doc.addImage(sealData, 'PNG', 15, 10, 30, 30);
        doc.addImage(kanjiData, 'PNG', 160, 10, 35, 35);

        doc.saveGraphicsState();
        doc.setGState(new doc.GState({opacity: 0.15}));
        doc.addImage(sealData, 'PNG', 45, 80, 120, 120);
        doc.restoreGraphicsState();
    } catch (err) {
        console.error(err);
    }

    doc.setFontSize(10);
    doc.setFont("helvetica", "normal");
    doc.text("Republic of the Namek", 105, 15, { align: "center" });
    doc.text("City of Goku", 105, 20, { align: "center" });
    doc.text("Barangay XYZ", 105, 25, { align: "center" });

    doc.setFontSize(16);
    doc.text("OFFICE OF PUNONG BARANGAY", 105, 45, { align: "center" });
    doc.setFont("helvetica", "bold");
    doc.setFontSize(20);
    doc.text("BARANGAY VENDOR PERMIT", 105, 55, { align: "center" });

    doc.setFontSize(11);
    doc.setFont("helvetica", "normal");
    doc.text("Nature of Business:", 20, 75);
    doc.setFont("helvetica", "bold");
    doc.text(productType.toUpperCase(), 70, 75);

    const infoY = 90;
    doc.setFont("helvetica", "normal");
    doc.text("Proprietor:", 20, infoY);
    doc.text("Permit Number:", 20, infoY + 7);
    doc.text("Address:", 20, infoY + 14);
    doc.text("Business Location:", 20, infoY + 21);
    doc.text("Status:", 20, infoY + 28);

    doc.setFont("helvetica", "bold");
    doc.text(vendorName.split(' ')[0].toUpperCase(), 70, infoY);
    doc.text(`BRGY-VEND-2026-069`, 70, infoY + 7);
    doc.text("Brgy. XYZ, Namek", 70, infoY + 14);
    doc.text("Mobile within Brgy. Namek", 70, infoY + 21);
    doc.text("Operating", 70, infoY + 28);

    doc.text(`Valid Until: December 30, 2027`, 135, infoY + 21);
    doc.text("Amount Paid: PHP 100.00", 135, infoY + 28);

    doc.setFont("helvetica", "normal");
    doc.setFontSize(10);
    const bodyText = [
        "This Permit is being issued subject to existing rules and regulations, provided however, that the necessary fees are paid to the Treasurer of the Barangay as assessed.",
        "This is non-transferable and shall be deemed null and void upon failure by the owner to follow the said rules and regulations set forth by the Local Government Unit of Goku, Namek."
    ];

    let currentY = 145;
    bodyText.forEach(line => {
        const splitText = doc.splitTextToSize(line, 170);
        // Add indentation to the first line of the paragraph
        doc.text(splitText[0], 30, currentY);
        for(let i=1; i<splitText.length; i++) {
            doc.text(splitText[i], 20, currentY + (i*5));
        }
        currentY += (splitText.length * 5) + 10;
    });

    doc.text(`GIVEN this 30th day of December, 2026 at Brgy XYZ, Goku, Namek`, 105, currentY + 5, { align: "center" });

    doc.setFont("helvetica", "bold");
    doc.text("SON GOHAN", 150, 210);
    doc.line(140, 211, 185, 211);
    doc.setFont("helvetica", "normal");
    doc.text("Punong Barangay", 148, 216);

    doc.setFont("helvetica", "bold");
    doc.text(vendorName.toUpperCase(), 45, 225, { align: "center" });
    doc.line(20, 226, 70, 226);
    doc.setFont("helvetica", "normal");
    doc.text("Owner", 45, 231, { align: "center" });

    // Certificate Box
    doc.setLineWidth(0.5);
    doc.line(120, 260, 180, 250); // bottom edge
    doc.line(116, 235, 176, 225); // top edge
    doc.line(116, 235, 120, 260); // left edge
    doc.line(176, 225, 180, 250); // right edge

    doc.setFontSize(9);
    doc.setFont("helvetica", "bold");
    doc.text("CERTIFIED TRUE COPY", 121, 240, { angle: 9.46 });
    doc.setFont("helvetica", "normal");
    doc.text("Signed: ________________", 119, 250, { angle: 9.46 });
    doc.text("Date:   ________________", 118, 257, { angle: 9.46 });

    doc.save(`Barangay-Permit_${vendorName}.pdf`);
};

document.addEventListener("DOMContentLoaded", function() {
    const downloadBtns = document.querySelectorAll('.download-permit-btn, #downloadPermitApproved');
    downloadBtns.forEach(btn => {
        btn.addEventListener('click', window.generatePermitPDF);
    });
});

// Add validation before form submission
function validateVendorForm() {
    const businessName = document.getElementById('business-name').value.trim();
    const ownerName = document.getElementById('vendor-name').value.trim();
    const businessType = document.getElementById('product-category').value.trim();
    const contactNumber = document.getElementById('contact-number').value.trim();
    const address = document.getElementById('home-address').value.trim();

    if (!businessName || !ownerName || !businessType || !contactNumber || !address) {
        showToast('All fields are required. Please fill out the form completely.');
        return false;
    }

    if (!/^\d{10,11}$/.test(contactNumber)) {
        showToast('Invalid contact number. Please enter a valid 10-11 digit number.');
        return false;
    }

    return true;
}
