<?php

namespace Database\Seeders;

use App\Models\Advice;
use App\Models\AnesthesiaRecord;
use App\Models\ClinicalFeature;
use App\Models\ClinicalSeal;
use App\Models\Complaint;
use App\Models\DrugHistory;
use App\Models\FamilyHistory;
use App\Models\LaboratoryTest;
use App\Models\MedicalHistoryCondition;
use App\Models\MenstrualHistory;
use App\Models\OtNote;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedComplaints();
        $this->seedTests();
        $this->seedMedicalHistory();
        $this->seedAdvice();
        $this->seedClinicalSeals();
        $this->seedClinicalFeatures();
        $this->seedFamilyHistories();
        $this->seedMenstrualHistories();
        $this->seedDrugHistories();
        $this->seedOtNotes();
        $this->seedAnesthesiaRecords();
    }

    private function seedComplaints(): void
    {
        $complaints = [
            // General Medicine
            'Fever', 'Cold', 'Cough', 'Headache', 'Body Ache', 'Fatigue', 'Weakness',
            'Dizziness', 'Nausea', 'Vomiting', 'Diarrhea', 'Constipation', 'Loss of Appetite',
            'Weight Loss', 'Weight Gain', 'Sweating', 'Chills', 'Insomnia', 'Malaise',
            'Unwell Feeling',

            // Respiratory
            'Breathlessness', 'Wheezing', 'Chest Tightness', 'Sore Throat',
            'Throat Pain', 'Hoarseness of Voice', 'Nasal Congestion', 'Runny Nose',
            'Sneezing', 'Post Nasal Drip', 'Difficulty Swallowing',

            // Cardiovascular
            'Chest Pain', 'Palpitation', 'Swelling of Legs', 'High Blood Pressure',
            'Low Blood Pressure', 'Irregular Heartbeat', 'Varicose Veins',

            // Gastrointestinal
            'Abdominal Pain', 'Stomach Pain', 'Bloating', 'Gas', 'Acidity',
            'Heartburn', 'Indigestion', 'Loss of Taste', 'Loss of Smell',
            'Blood in Stool', 'Piles Pain', 'Frequent Urination', 'Burning Micturition',

            // Musculoskeletal
            'Back Pain', 'Neck Pain', 'Joint Pain', 'Knee Pain', 'Shoulder Pain',
            'Muscle Cramps', 'Stiffness', 'Swelling of Joints', 'Reduced Range of Motion',
            'Heel Pain', 'Wrist Pain', 'Hip Pain',

            // Neurological
            'Numbness', 'Tingling', 'Tremor', 'Seizure', 'Fainting',
            'Memory Loss', 'Confusion', 'Difficulty Concentrating', 'Migraine',
            'Vertigo', 'Bell\'s Palsy',

            // Dermatological
            'Skin Rash', 'Itching', 'Acne', 'Pimples', 'Hair Fall',
            'Dry Skin', 'Oily Skin', 'Skin Darkening', 'Warts', 'Fungal Infection',
            'Eczema', 'Psoriasis', 'Urticaria', 'Burns', 'Wound Not Healing',

            // ENT
            'Ear Pain', 'Ear Discharge', 'Hearing Loss', 'Tinnitus',
            'Nose Bleed', 'Sinus Pain', 'Tonsillitis', 'Adenoid Problem',

            // Eye
            'Eye Pain', 'Red Eyes', 'Blurred Vision', 'Watering of Eyes',
            'Dry Eyes', 'Floaters', 'Vision Loss',

            // Gynecology
            'Irregular Periods', 'Heavy Periods', 'Painful Periods', 'Amenorrhea',
            'White Discharge', 'Lower Abdominal Pain in Women', 'Infertility',
            'Morning Sickness', 'Morning Sickness in Pregnancy',

            // Pediatrics
            'Poor Feeding in Children', 'Delayed Milestones', 'Recurrent Infections',
            'Bedwetting', 'Behavioral Problems', 'ADHD Symptoms',

            // Urological
            'Kidney Stone Pain', 'Blood in Urine', 'Difficulty Urinating',
            'Prostate Enlargement Symptoms', 'Testicular Pain',

            // Endocrine
            'Excessive Thirst', 'Frequent Urination in Diabetics',
            'Heat Intolerance', 'Cold Intolerance', 'Hair Thinning',
            'Goiter',

            // Psychiatric
            'Anxiety', 'Depression', 'Panic Attacks', 'Mood Swings',
            'Stress', 'Anger Issues',

            // Allergic
            'Allergic Rhinitis', 'Drug Allergy', 'Food Allergy',
            'Insect Bite Reaction', 'Anaphylaxis History',

            // Dental
            'Toothache', 'Gum Bleeding', 'Loose Tooth', 'Mouth Ulcer',
            'Jaw Pain',

            // Others
            'Swollen Lymph Nodes', 'Night Sweats', 'Cough with Blood',
            'Leg Ulcer', 'Non-Healing Wound', 'Growth or Lump',
            'Post-Surgical Pain', 'Trauma', 'Injury',
        ];

        foreach ($complaints as $name) {
            Complaint::findByNameOrCreate($name);
        }

        $this->command->info('✓ Complaints seeded: ' . count($complaints) . ' items');
    }

    private function seedTests(): void
    {
        $tests = [
            // Hematology
            'Complete Blood Count (CBC)', 'Hemoglobin', 'Total Leucocyte Count (TLC)',
            'Differential Leucocyte Count (DLC)', 'Platelet Count', 'Erythrocyte Sedimentation Rate (ESR)',
            'Peripheral Blood Smear', 'Reticulocyte Count', 'Hematocrit',
            'Mean Corpuscular Volume (MCV)', 'Mean Corpuscular Hemoglobin (MCH)',
            'Mean Corpuscular Hemoglobin Concentration (MCHC)', 'Red Cell Distribution Width (RDW)',
            'Blood Group & Rh Factor', 'Coagulation Profile', 'Prothrombin Time (PT)',
            'INR', 'Activated Partial Thromboplastin Time (APTT)', 'Bleeding Time',
            'Clotting Time', 'D-Dimer',

            // Biochemistry
            'Fasting Blood Sugar (FBS)', 'Post Prandial Blood Sugar (PPBS)',
            'Random Blood Sugar (RBS)', 'HbA1c (Glycated Hemoglobin)',
            'Oral Glucose Tolerance Test (OGTT)', 'Serum Creatinine', 'Blood Urea Nitrogen (BUN)',
            'Uric Acid', 'Serum Electrolytes (Na, K, Cl)', 'Serum Sodium',
            'Serum Potassium', 'Serum Chloride', 'Serum Calcium', 'Serum Phosphorus',
            'Serum Magnesium', 'Serum Iron', 'Serum Ferritin', 'Total Iron Binding Capacity (TIBC)',
            'Serum Zinc', 'Serum Copper',

            // Liver Function Tests
            'Liver Function Test (LFT)', 'Serum Bilirubin (Total)', 'Serum Bilirubin (Direct)',
            'Serum Bilirubin (Indirect)', 'SGOT (AST)', 'SGPT (ALT)', 'Alkaline Phosphatase (ALP)',
            'Gamma Glutamyl Transferase (GGT)', 'Total Protein', 'Serum Albumin',
            'Serum Globulin', 'A/G Ratio', 'LDH (Lactate Dehydrogenase)',

            // Kidney Function Tests
            'Kidney Function Test (KFT)', 'Blood Urea', 'Serum Creatinine',
            'Blood Urea Nitrogen', 'eGFR (Estimated Glomerular Filtration Rate)',
            'Cystatin C',

            // Lipid Profile
            'Lipid Profile', 'Total Cholesterol', 'HDL Cholesterol',
            'LDL Cholesterol', 'Triglycerides', 'VLDL Cholesterol',
            'Total Cholesterol / HDL Ratio',

            // Thyroid
            'Thyroid Function Test (TFT)', 'TSH (Thyroid Stimulating Hormone)',
            'Free T3', 'Free T4', 'Total T3', 'Total T4',
            'Anti-TPO Antibodies', 'Anti-Thyroglobulin Antibodies',

            // Urine Tests
            'Urinalysis', 'Urine Routine & Microscopy', 'Urine Culture & Sensitivity',
            'Urine Albumin', 'Urine Microalbumin', 'Urine Sugar',
            'Urine Ketones', 'Urine Bilirubin', 'Urine PCR (Protein Creatinine Ratio)',
            '24 Hour Urine Protein', 'Urine Calcium', 'Urine Phosphorus',
            'Urine Uric Acid', 'Urine Sodium',

            // Stool Tests
            'Stool Routine Examination', 'Stool Occult Blood Test',
            'Stool Culture', 'Stool for Ova & Parasites',
            'Stool for Reducing Substance', 'Stool C-Difficile Toxin',

            // Cardiac Markers
            'Cardiac Profile', 'Troponin I', 'Troponin T',
            'CK-MB (Creatine Kinase-MB)', 'BNP (Brain Natriuretic Peptide)',
            'NT-Pro BNP', 'High Sensitivity CRP (hs-CRP)',

            // Diabetes
            'Diabetes Profile', 'Fasting Insulin', 'C-Peptide',
            'Glucose-6-Phosphate Dehydrogenase (G6PD)',

            // Liver & Hepatitis
            'Hepatitis Panel', 'HBsAg (Hepatitis B Surface Antigen)',
            'Anti-HCV (Hepatitis C Antibody)', 'Anti-HAV IgM',
            'HBeAg', 'Anti-HBe', 'HBV DNA', 'HCV RNA',
            'Liver Fibroscan',

            // Tumor Markers
            'PSA (Prostate Specific Antigen)', 'AFP (Alpha Fetoprotein)',
            'CEA (Carcinoembryonic Antigen)', 'CA-125',
            'CA 19-9', 'CA 15-3', 'Beta-HCG', 'LDH Tumor Marker',

            // Vitamins & Minerals
            'Vitamin D (25-Hydroxy)', 'Vitamin B12', 'Vitamin B6',
            'Folate', 'Vitamin A', 'Vitamin C', 'Vitamin E',
            'Vitamin K', 'Serum Folate', 'RBC Folate',

            // Infectious Disease
            'Widal Test', 'Malaria Parasite (MP) Test', 'Dengue NS1 Antigen',
            'Dengue IgG & IgM', 'Typhoid IgG', 'HIV I & II',
            'VDRL (Syphilis)', 'RPR Test', 'TB Gold Test (Quantiferon)',
            'Mantoux Test (PPD)', 'ASO Titer (Anti-Streptolysin O)',
            'CRP (C-Reactive Protein)', 'Procalcitonin',

            // Hormonal
            'Insulin Fasting', 'Insulin Post Prandial',
            'Cortisol', 'ACTH (Adrenocorticotropic Hormone)',
            'Testosterone', 'Free Testosterone', 'SHBG (Sex Hormone Binding Globulin)',
            'Estradiol', 'Progesterone', 'LH (Luteinizing Hormone)',
            'FSH (Follicle Stimulating Hormone)', 'Prolactin',
            'Growth Hormone', 'IGF-1 (Insulin-like Growth Factor 1)',
            'Aldosterone', 'Renin', 'Parathyroid Hormone (PTH)',

            // Immunology
            'ANA (Antinuclear Antibody)', 'Anti-dsDNA',
            'Rheumatoid Factor (RF)', 'Anti-CCP Antibody',
            'Complement C3', 'Complement C4', 'Immunoglobulin IgG',
            'Immunoglobulin IgA', 'Immunoglobulin IgM',
            'ANCA (Anti-Neutrophil Cytoplasmic Antibody)',

            // Radiology / Imaging
            'Chest X-Ray (PA View)', 'Chest X-Ray (AP View)',
            'Abdominal X-Ray', 'X-Ray Affected Part',
            'Ultrasound Abdomen', 'Ultrasound Pelvis',
            'Ultrasound Whole Abdomen', 'Ultrasound KUB (Kidney, Ureter, Bladder)',
            'Ultrasound Thyroid', 'Ultrasound Breast',
            'Ultrasound Scrotum', 'Ultrasound Obstetric',
            'CT Scan Head', 'CT Scan Abdomen', 'CT Scan Chest',
            'CT Scan KUB', 'CT Scan with Contrast', 'CT Scan without Contrast',
            'MRI Brain', 'MRI Spine (Cervical)', 'MRI Spine (Lumbar)',
            'MRI Knee Joint', 'MRI Abdomen', 'MRI Pelvis',
            'MRA (Magnetic Resonance Angiography)',
            'Doppler Study - Lower Limb', 'Doppler Study - Neck Vessels',
            'Doppler Study - Upper Limb', 'Echocardiography',
            'ECG (Electrocardiography)', 'EEG (Electroencephalography)',
            'EMG (Electromyography)', 'Pulmonary Function Test (PFT)',
            'DEXA Scan (Bone Density)', 'Mammography',
            'Treadmill Test (TMT)', 'Holter Monitoring',

            // Microbiology
            'Blood Culture', 'Sputum Culture', 'Urine Culture',
            'Throat Swab Culture', 'Wound Swab Culture',
            'High Vaginal Swab (HVS)', 'Stool Culture',
            'CSF Analysis', 'Pleural Fluid Analysis',
            'Ascitic Fluid Analysis', 'Joint Fluid Analysis',
            'AFB Smear (Acid Fast Bacilli)', 'Gram Stain',
            'ZN Stain (Ziehl-Neelsen)', 'GeneXpert MTB/RIF',
            'Cultures & Sensitivity',

            // Histopathology / Biopsy
            'Fine Needle Aspiration Cytology (FNAC)',
            'Biopsy Histopathology', 'Pap Smear',
            'Endometrial Biopsy', 'Bone Marrow Biopsy',

            // Others
            'Arterial Blood Gas (ABG)', 'Venous Blood Gas (VBG)',
            'Sweat Chloride Test', 'Phenylketonuria (PKU)',
            'Newborn Screening', 'Allergy Panel',
            'Genetic Testing', 'Karyotyping',
        ];

        foreach ($tests as $name) {
            LaboratoryTest::findByNameOrCreate($name);
        }

        $this->command->info('✓ Tests/Investigations seeded: ' . count($tests) . ' items');
    }

    private function seedMedicalHistory(): void
    {
        $conditions = [
            // Chronic Diseases
            'Diabetes Mellitus Type 2', 'Diabetes Mellitus Type 1',
            'Hypertension', 'Hypotension', 'Hyperlipidemia',
            'Hypothyroidism', 'Hyperthyroidism', 'Subclinical Hypothyroidism',
            'Bronchial Asthma', 'COPD (Chronic Obstructive Pulmonary Disease)',
            'Coronary Artery Disease', 'Congestive Heart Failure',
            'Atrial Fibrillation', 'Rheumatic Heart Disease',
            'Chronic Kidney Disease (CKD)', 'Chronic Liver Disease',
            'Cirrhosis of Liver', 'Fatty Liver Disease',
            'Chronic Gastritis', 'GERD (Gastroesophageal Reflux Disease)',
            'Peptic Ulcer Disease', 'Irritable Bowel Syndrome (IBS)',
            'Inflammatory Bowel Disease', 'Ulcerative Colitis',
            'Crohn\'s Disease', 'Celiac Disease',
            'Chronic Pancreatitis', 'Gallstones', 'Kidney Stones',
            'Uric Acid/Gout', 'Rheumatoid Arthritis',
            'Osteoarthritis', 'Ankylosing Spondylitis',
            'Systemic Lupus Erythematosus (SLE)',
            'Psoriasis', 'Eczema', 'Vitiligo',
            'Epilepsy/Seizure Disorder', 'Migraine',
            'Parkinson\'s Disease', 'Alzheimer\'s Disease',
            'Multiple Sclerosis', 'Myasthenia Gravis',
            'Sickle Cell Disease', 'Thalassemia',
            'Aplastic Anemia', 'Leukemia',
            'Lymphoma', 'Multiple Myeloma',
            'Glaucoma', 'Cataract', 'Macular Degeneration',
            'Benign Prostatic Hyperplasia (BPH)',
            'Polycystic Ovary Syndrome (PCOS)',
            'Endometriosis', 'Fibroids (Uterine)',
            'Osteoporosis', 'Osteopenia',
            'Pulmonary Tuberculosis', 'Extrapulmonary Tuberculosis',
            'Obstructive Sleep Apnea',
            'Restless Leg Syndrome',

            // Previous Surgeries
            'Appendectomy', 'Cholecystectomy (Gallbladder Removal)',
            'Hernia Repair', 'Hysterectomy',
            'C-Section (Caesarean Delivery)',
            'Normal Vaginal Delivery', 'Laparoscopic Surgery',
            'Knee Replacement', 'Hip Replacement',
            'Spinal Surgery', 'Discectomy',
            'Coronary Artery Bypass Graft (CABG)',
            'Angioplasty with Stent', 'Pacemaker Implantation',
            'Tonsillectomy', 'Adenoidectomy',
    'Sinus Surgery', 'Septoplasty',
    'Mastectomy', 'Lumpectomy',
    'Thyroidectomy', 'Parotidectomy',
    'Hemorrhoidectomy', 'Fistulectomy',
    'Cataract Surgery', 'Lasik Surgery',
    'Tympanoplasty', 'Myringotomy',
    'Hydrocele Repair', 'Varicocelectomy',
    'Circumcision', 'Vasectomy',
    'Dilation & Curettage (D&C)',
    'TURP (Transurethral Resection of Prostate)',
    'Laparoscopic Nephrectomy', 'Cystectomy',

    // Allergies
    'Penicillin Allergy', 'Amoxicillin Allergy',
    'Sulfa Drug Allergy', 'Aspirin Allergy',
    'NSAID Allergy', 'Iodine Contrast Allergy',
    'Latex Allergy', 'Dust Allergy',
    'Pollen Allergy', 'Food Allergy',
    'Shellfish Allergy', 'Egg Allergy',
    'Milk/Dairy Allergy', 'Nut Allergy',
    'Soy Allergy', 'Wheat/Gluten Allergy',
    'Insect Sting Allergy', 'Cockroach Allergy',
    'Mold Allergy', 'Pet Dander Allergy',
    'Corticosteroid Allergy', 'Local Anesthetic Allergy',
    'Cough Syrup Allergy', 'Vaccine Allergy',
    'Blood Transfusion Reaction',

    // Lifestyle History
    'Current Smoker', 'Ex-Smoker',
    'Alcohol Consumer', 'Ex-Alcohol Consumer',
    'Regular Exercise', 'Sedentary Lifestyle',
    'Vegetarian Diet', 'Vegan Diet',
    'High Salt Diet', 'High Sugar Diet',
    'Fast Food Consumer', 'Betel Nut User',
    'Drug Abuse History', 'IV Drug User',
    'Obesity', 'Underweight',
    'Normal BMI',

    // Family History
    'Family History of Diabetes', 'Family History of Hypertension',
    'Family History of Heart Disease', 'Family History of Stroke',
    'Family History of Cancer', 'Family History of Asthma',
    'Family History of TB', 'Family History of Mental Illness',
    'Family History of Kidney Disease', 'Family History of Liver Disease',
    'Family History of Thyroid Disorder', 'Family History of Arthritis',
    'Family History of Glaucoma', 'Family History of Cataract',
    'Family History of Epilepsy', 'Family History of Sickle Cell Disease',
    'Family History of Thalassemia', 'Family History of Obesity',
    'Family History of Kidney Stones', 'Family History of Gout',

    // Obstetric History
    'Gravida', 'Para', 'Primigravida',
    'Multigravida', 'Abortions',
    'Ectopic Pregnancy', 'Molar Pregnancy',
    'Previous Preeclampsia', 'Gestational Diabetes',

    // Immunization History
    'BCG Vaccinated', 'Polio Vaccinated',
    'Hepatitis B Vaccinated', 'COVID-19 Vaccinated',
    'Influenza Vaccinated', 'Pneumococcal Vaccinated',
    'Tetanus Vaccinated', 'HPV Vaccinated',

    // Blood Transfusion History
    'Blood Transfusion History',
    'Multiple Blood Transfusions',

    // Travel History
    'Recent Foreign Travel', 'Endemic Area Travel',
    'Malaria Endemic Area Visit',

    // Social History
    'Exposed to Industrial Chemicals', 'Occupational Hazard Exposure',
    'Exposed to Asbestos', 'Exposed to Radiation',
        ];

        foreach ($conditions as $name) {
            MedicalHistoryCondition::findByNameOrCreate($name);
        }

        $this->command->info('✓ Past Medical History seeded: ' . count($conditions) . ' items');
    }

    private function seedAdvice(): void
    {
        $advices = [
            // ═══ General Health (সাধারণ স্বাস্থ্য) ═══
            'পর্যাপ্ত পানি পান করুন (প্রতিদিন ৮-১০ গ্লাস)',
            'পর্যাপ্ত বিশ্রাম নিন (প্রতিদিন ৭-৮ ঘণ্টা ঘুম)',
            'সময়মতো ওষুধ সেবন করুন',
            'নিয়মিত ব্যায়াম করুন (প্রতিদিন ৩০ মিনিট)',
            'সুষম ও পুষ্টিকর খাদ্য খান',
            'ফলমূল ও শাকসবজি বেশি খান',
            'রান্নার তেল কম ব্যবহার করুন',
            'ঘরের খাবার বেশি খান, বাইরের খাবার এড়িয়ে চলুন',
            'চিনি ও লবণ কম খান',
            'প্রতিদিন হাঁটুন এবং শরীরচর্চা করুন',
            'প্রতিদিন সকালে খালি পেটে গরম পানি পান করুন',
            'পরিষ্কার-পরিচ্ছন্নতা বজায় রাখুন',
            'রোগ প্রতিরোধক ক্ষমতা বাড়ানোর জন্য ভিটামিন সি সমৃদ্ধ খাবার খান',

            // ═══ Lifestyle (জীবনযাত্রা) ═══
            'ধূমপান পরিহার করুন',
            'মদ্যপান পরিহার করুন',
            'বিশ্রাম নিন এবং স্ট্রেস কমান',
            'পর্যাপ্ত ঘুম নিন',
            'রাতে দেরিতে খাবার খাবেন না',
            'ফাস্টফুড এড়িয়ে চলুন',
            'বাসায় রান্না করা খাবার খান',
            'নিয়মিত সকালে হাঁটুন',
            'সন্ধ্যায় হালকা ব্যায়াম করুন',
            'মোবাইল ফোনের ব্যবহার কমান',
            'রাতে ঘুমানোর ২ ঘণ্টা আগে স্ক্রিন ব্যবহার বন্ধ করুন',
            'নিয়মিত সময়ে খাবার খান, খালি পেটে থাকবেন না',
            'হাঁটাহাঁটি বা সাইকেল চালানোর অভ্যাস করুন',
            'লিফটের পরিবর্তে সিঁড়ি ব্যবহার করুন',
            'পরিবারের সাথে বিনোদনমূলক সময় কাটান',

            // ═══ Medicine-Related (ওষুধ সম্পর্কিত) ═══
            'ওষুধ ডাক্তারের পরামর্শ অনুযায়ী সেবন করুন',
            'ওষুধ খালি পেটে খাবেন না (যদি না বলা হয়)',
            'ওষুধের মাত্রা নিজে পরিবর্তন করবেন না',
            'এন্টিবায়োটিক পুরো কোর্স শেষ করুন',
            'ওষুধ ফেলে দিবেন না, শিশুর নাগালের বাইরে রাখুন',
            'ওষুধের সময়সূচী মেনে চলুন',
            'এক সাথে একাধিক ওষুধ খাবেন না (ডাক্তারের পরামর্শ ছাড়া)',
            'ওষুধের কোনো পার্শ্বপ্রতিক্রিয়া হলে ডাক্তারকে জানান',
            'ওষুধ পানি দিয়ে খান, চা বা কফি দিয়ে নয়',
            'ওষুধের মেয়াদ শেষ হলে ফেলে দিন, ব্যবহার করবেন না',
            'ডাক্তারের পরামর্শ ছাড়া বাজারের ওষুধ খাবেন না',
            'ওষুধ সংরক্ষণের জন্য ছায়াযুক্ত শুষ্ক স্থান বেছে নিন',
            'ইনজেকশন বা সিরিপ খোলার পর ফ্রিজে রাখুন (যদি বলা হয়)',
            'ডোজ ভুলে গেলে পরের ডোজের সাথে ডাবল খাবেন না',

            // ═══ Diet / খাদ্যতালিকা ═══
            'মৃদু খাবার খান, ভারী খাবার এড়িয়ে চলুন',
            'জল ফল বেশি খান',
            'তাজা খাবার খান, বাসি খাবার এড়িয়ে চলুন',
            'প্রচুর সালাদ খান',
            'ফাইবারযুক্ত খাবার খান',
            'ক্যালসিয়াম সমৃদ্ধ খাবার খান (দুধ, দই, পনির)',
            'আয়রন সমৃদ্ধ খাবার খান (পালং শাক, কচুশাক, মাংস)',
            'ভিটামিন সি সমৃদ্ধ খাবার খান (লেবু, কমলা, আম)',
            'প্রোবায়োটিক খাবার খান (দই, আচার)',
            'গরম পানি পান করুন',
            'তৈলাক্ত ও ভাজাপোড়া খাবার কম খান',
            'প্রতিদিন অন্তত একবার সবুজ শাকসবজি খান',
            'প্রোটিন সমৃদ্ধ খাবার খান (ডিম, মাছ, মুরগি, ডাল)',
            'রান্নায় লবণ কম ব্যবহার করুন',
            'মিষ্টি পানীয় ও জুস এড়িয়ে চলুন',
            'খাবার আধোগত খান, তাড়াহুড়ো করে খাবেন না',
            'রাতে হালকা খাবার খান, ভারী খাবার এড়িয়ে চলুন',
            'অঙ্গরব্জরের জুস বা শরবত পান করুন',
            'বাদাম ও খেজুর পরিমিত পরিমাণে খান',

            // ═══ Diabetes / ডায়াবেটিস ═══
            'ডায়াবেটিস: প্রতিদিন রক্তে শর্করা পরীক্ষা করুন',
            'ডায়াবেটিস: মিষ্টি খাবার এড়িয়ে চলুন',
            'ডায়াবেটিস: পায়ের যত্ন নিন',
            'ডায়াবেটিস: ইনসুলিন সঠিকভাবে সংরক্ষণ করুন',
            'ডায়াবেটিস: HbA1c নিয়মিত পরীক্ষা করুন',
            'ডায়াবেটিস: পায়ের ক্ষত দেখলে ডাক্তার দেখান',
            'ডায়াবেটিস: গ্লাইসেমিক ইন্ডেক্স কম খাবার খান',
            'ডায়াবেটিস: সাদা ভাতের পরিবর্তে খাচ্চাভাত খান',
            'ডায়াবেটিস: প্রতিদিন একই সময়ে খাবার খান',
            'ডায়াবেটিস: খাবারের আগে ওষুধ খান (যদি বলা হয়)',
            'ডায়াবেটিস: পায়ের নখ সঠিকভাবে কাটুন',
            'ডায়াবেটিস: জুতা সবসময় পরুন, খালি পায়ে হাঁটবেন না',

            // ═══ Hypertension / উচ্চ রক্তচাপ ═══
            'উচ্চ রক্তচাপ: লবণ কম খান',
            'উচ্চ রক্তচাপ: নিয়মিত রক্তচাপ পরীক্ষা করুন',
            'উচ্চ রক্তচাপ: ওষুধ বাদ করবেন না',
            'উচ্চ রক্তচাপ: স্ট্রেস কমান',
            'উচ্চ রক্তচাপ: তৈলাক্ত খাবার এড়িয়ে চলুন',
            'উচ্চ রক্তচাপ: প্রতিদিন ৩০ মিনিট হাঁটুন',
            'উচ্চ রক্তচাপ: ওজন নিয়ন্ত্রণে রাখুন',
            'উচ্চ রক্তচাপ: ধূমপান বন্ধ করুন',
            'উচ্চ রক্তচাপ: পুষ্টিকর খাবার খান, ফলমূল বেশি খান',
            'উচ্চ রক্তচাপ: ডাক্তারের পরামর্শ ছাড়া ওষুধ বন্ধ করবেন না',

            // ═══ Asthma / হাঁপানি ═══
            'হাঁপানি: ধুলাবালি এড়িয়ে চলুন',
            'হাঁপানি: ইনহেলার সবসময় সাথে রাখুন',
            'হাঁপানি: ঠান্ডা পানি এড়িয়ে চলুন',
            'হাঁপানি: ধূমপান একদম বন্ধ করুন',
            'হাঁপানি: ঘরের পরিবেশ পরিষ্কার রাখুন',
            'হাঁপানি: নরম তোয়ালে ব্যবহার করুন, পালকি তোয়ালে এড়িয়ে চলুন',
            'হাঁপানি: শীতকালে মুখোশ পরে বের হোন',
            'হাঁপানি: হালকা ব্যায়াম করুন, অতিরিক্ত পরিশ্রম এড়িয়ে চলুন',
            'হাঁপানি: ঠান্ডা ও ঘরুণ আবহাওয়ায় বাইরে যাওয়া কমান',

            // ═══ Skin Care / ত্বকের যত্ন ═══
            'ত্বক: প্রচুর পানি পান করুন',
            'ত্বক: সানস্ক্রিন ব্যবহার করুন (বাইরে যাওয়ার সময়)',
            'ত্বক: ময়দন ও সাবান পরিমিত ব্যবহার করুন',
            'ত্বক: খিকখিকে ত্বকে ডাক্তারের পরামর্শ ছাড়া ক্রিম ব্যবহার করবেন না',
            'ত্বক: পরিষ্কার ও শুষ্ক রাখুন',
            'ত্বক: তৈলাক্ত ত্বকে হালকা ময়েশ্চারাইজার ব্যবহার করুন',
            'ত্বক: ঘাম পোঁড়া হলে সাবধানে পরিষ্কার করুন',
            'ত্বক: নখ কাটুন ও হাত-পায়ের নখ পরিষ্কার রাখুন',
            'ত্বক: রোদে বের হলে টুপি বা ছাতা ব্যবহার করুন',
            'ত্বক: পরিষ্কার কাপড় পরুন, অপরিষ্কার কাপড় পরবেন না',

            // ═══ Follow-up / ফলো-আপ ═══
            'প্রয়োজনে ফলো-আপ করুন',
            '৭ দিন পর ফলো-আপে আসুন',
            '১৪ দিন পর ফলো-আপে আসুন',
            '১ মাস পর ফলো-আপে আসুন',
            '৩ মাস পর ফলো-আপে আসুন',
            '৬ মাস পর ফলো-আপে আসুন',
            '১ বছর পর ফলো-আপে আসুন',
            'অবস্থা খারাপ হলে তৎক্ষণাৎ হাসপাতালে যান',
            'জরুরি অবস্থায় ৯৯৯ নম্বরে কল করুন',
            'পরীক্ষার রিপোর্ট নিয়ে ফলো-আপে আসুন',
            'প্রেসক্রিপশন সহ ফলো-আপে আসুন',
            'অবস্থা উন্নত হলেও ফলো-আপে আসুন',
            'ফলো-আপে আসার সময় মিস করবেন না',

            // ═══ Pregnancy / গর্ভাবস্থা ═══
            'গর্ভাবস্থায় ফলো-আপ নিয়মিত করুন',
            'গর্ভাবস্থায় ফলিক এসিড সেবন করুন',
            'গর্ভাবস্থায় আয়রন সাপ্লিমেন্ট খান',
            'গর্ভাবস্থায় ভারী কাজ এড়িয়ে চলুন',
            'গর্ভাবস্থায় ধূমপান ও মদ্যপান একদম নিষেধ',
            'গর্ভাবস্থায় নিয়মিত হাঁটুন',
            'গর্ভাবস্থায় পর্যাপ্ত পানি পান করুন',
            'গর্ভাবস্থায় মাছ ও মাংস ভালো করে রান্না করে খান',
            'গর্ভাবস্থায় কাঁচা বা অর্ধসিদ্ধ খাবার খাবেন না',
            'গর্ভাবস্থায় ঔষধ ডাক্তারের পরামর্শ ছাড়া খাবেন না',
            'গর্ভাবস্থায় নিয়মিত রক্তচাপ ও রক্তে শর্করা পরীক্ষা করুন',
            'গর্ভাবস্থায় অতিরিক্ত চিন্তা কমান, মানসিক প্রশান্তি রাখুন',
            'গর্ভাবস্থায় যৌন সম্পর্কে সতর্ক থাকুন (ডাক্তারের পরামর্শ নিন)',
            'গর্ভাবস্থায় টিকা সময়মতো নিন',
            'গর্ভাবস্থায় হাইড্রেটেড থাকুন, পানি ও তরল খাবার বেশি খান',

            // ═══ Mental Health / মানসিক স্বাস্থ্য ═══
            'মানসিক স্বাস্থ্যের দিকে খেয়াল রাখুন',
            'স্ট্রেস থেকে দূরে থাকুন',
            'প্রয়োজনে কাউন্সেলিং নিন',
            'পরিবারের সাথে সময় কাটান',
            'শখের কাজ করুন',
            'ধ্যান ও যোগব্যায়াম করুন',
            'গভীর শ্বাস প্রশ্বাসের ব্যায়াম করুন',
            'ইতিবাচক চিন্তা করুন',
            'প্রয়োজনে মানসিক স্বাস্থ্য বিশেষজ্ঞের সাথে কথা বলুন',
            'ঘুমের সময়সূচী নিয়মিত রাখুন',
            'অতিরিক্ত চিন্তা করা বন্ধ করুন',
            'প্রকৃতির সাথে সময় কাটান',

            // ═══ Prevention / প্রতিরোধ ═══
            'নিয়মিত স্বাস্থ্য পরীক্ষা করুন',
            'টিকা নিয়মিত নিন',
            'হাত ধুয়ে খাবার খান',
            'পরিষ্কার-পরিচ্ছন্নতা বজায় রাখুন',
            'মশা কামড় থেকে সাবধান হন',
            'রোগ প্রতিরোধক ব্যবস্থা অবলম্বন করুন',
            'নিয়মিত দাঁত পরিষ্কার করুন',
            'বছরে অন্তত একবার সম্পূর্ণ স্বাস্থ্য পরীক্ষা করান',
            'পানি ফিল্টার করে পান করুন',
            'বাইরে থেকে ফিরে হাত ধুয়ে মুখ ধুয়ে নিন',

            // ═══ Post-Surgery / অস্ত্রোপচার-পরবর্তী ═══
            'অস্ত্রোপচারের পর বিশ্রাম নিন',
            'অস্ত্রোপচারের পর ঘা পরিষ্কার রাখুন',
            'অস্ত্রোপচারের পর ওষুধ সঠিকভাবে সেবন করুন',
            'অস্ত্রোপচারের পর ফলো-আপ নিয়মিত করুন',
            'অস্ত্রোপচারের পর ভারী কাজ এড়িয়ে চলুন',
            'অস্ত্রোপচারের পর সার্জারির স্থানে পানি লাগবে না',
            'অস্ত্রোপচারের পর সুই বা স্ট্যাপল সঠিক সময়ে খুলতে হবে',
            'অস্ত্রোপচারের পর হাঁটাহাঁটি ধীরে ধীরে বাড়ান',
            'অস্ত্রোপচারের পর ক্ষতস্থানে ব্যথা বা ফুলে গেলে ডাক্তারকে জানান',

            // ═══ Children / শিশু পরিচর্যা ═══
            'শিশুদের নিয়মিত টিকা দিন',
            'শিশুদের পুষ্টিকর খাবার দিন',
            'শিশুদের পর্যাপ্ত ঘুম নিশ্চিত করুন',
            'শিশুদের সাথে খেলুন ও সময় কাটান',
            'শিশুদের স্ক্রিন টাইম কমান',
            'শিশুদের খাদ্যাভ্যাস ভালো করুন',
            'শিশুদের জ্বর হলে ঘন ঘন পানি খাওয়ান',
            'শিশুদের ডায়রিয়া হলে ORS পানি খাওয়ান',
            'শিশুদের শরীরে খরজ হলে সাবধানে যত্ন নিন',
            'শিশুদের পরিষ্কার-পরিচ্ছন্নতা বজায় রাখুন',
            'শিশুদের নখ কাটুন ও চুল পরিষ্কার রাখুন',
            'শিশুদের মাতার দুধ কম হলে ডাক্তারের পরামর্শ নিন',
            'শিশুদের ৬ মাস পর্যন্ত শুধু মায়ের দুধ খাওয়ান',
            'শিশুদের খাবারে লবণ ও চিনি কম দিন',
            'শিশুদের জ্ঞানীয় বিকাশের জন্য বই ও খেলনা দিন',

            // ═══ Elderly / বয়স্ক যত্ন ═══
            'বয়স্কদের পর্যাপ্ত বিশ্রাম নিশ্চিত করুন',
            'বয়স্কদের পায়ের যত্ন নিন',
            'বয়স্কদের খাবারে লবণ ও চিনি কমান',
            'বয়স্কদের নিয়মিত স্বাস্থ্য পরীক্ষা করান',
            'বয়স্কদের পড়ে যাওয়া থেকে রক্ষা করুন',
            'বয়স্কদের ওষুধ সময়মতো খাওয়ান',
            'বয়স্কদের হাঁটাহাঁটি করান (সম্ভব হলে)',
            'বয়স্কদের সামাজিক মিলনমেলা বজায় রাখুন',
            'বয়স্কদের দৃষ্টি ও শ্রবণ পরীক্ষা নিয়মিত করান',
            'বয়স্কদের একা থাকতে দেবেন না, সঙ্গ দিন',

            // ═══ Hypothyroid / হাইপোথাইরয়েড ═══
            'হাইপোথাইরয়েড: খালি পেটে থাইরয়েডের ওষুধ খান',
            'হাইপোথাইরয়েড: ওষুধ খাওয়ার ৩০ মিনিট পর খাবার খান',
            'হাইপোথাইরয়েড: নিয়মিত TSH পরীক্ষা করুন',
            'হাইপোথাইরয়েড: সয়াবিন ও আয়োডিনযুক্ত লবণ ব্যবহার করুন',
            'হাইপোথাইরয়েড: ওজন নিয়ন্ত্রণে রাখুন',

            // ═══ Cholesterol / কোলেস্টেরল ═══
            'কোলেস্টেরল: তৈলাক্ত খাবার এড়িয়ে চলুন',
            'কোলেস্টেরল: নিয়মিত ব্যায়াম করুন',
            'কোলেস্টেরল: ফাইবারযুক্ত খাবার খান',
            'কোলেস্টেরল: ওজন কমান',
            'কোলেস্টেরল: ধূমপান বন্ধ করুন',

            // ═══ Joint Pain / জয়েন্ট ব্যথা ═══
            'জয়েন্ট ব্যথা: ওজন কমান',
            'জয়েন্ট ব্যথা: হালকা ব্যায়াম করুন',
            'জয়েন্ট ব্যথা: গরম পানির সেচ দিন',
            'জয়েন্ট ব্যথা: ভারী বোঝা তোলা এড়িয়ে চলুন',
            'জয়েন্ট ব্যথা: ক্যালসিয়াম সমৃদ্ধ খাবার খান',
            'জয়েন্ট ব্যথা: জোর করে একই অবস্থানে বসে থাকবেন না',

            // ═══ Eye Care / চোখের যত্ন ═══
            'চোখ: স্ক্রিনের সামনে বেশি সময় কাটাবেন না',
            'চোখ: ২০-২০-২০ নিয়ম মেনে চলুন (প্রতি ২০ মিনিটে ২০ সেকেন্ড ২০ ফুট দূরে দেখুন)',
            'চোখ: সরাসরি রোদে চোখে কুচকুচে করে দেখবেন না',
            'চোখ: চোখের ব্যথা হলে ঘর্ষণ করবেন না',
            'চোখ: নিয়মিত চোখের পরীক্ষা করান',
            'চোখ: ভিটামিন এ সমৃদ্ধ খাবার খান (গাজর, পালংশাক)',
            'চোখ: পর্যাপ্ত ঘুম নিন',
            'চোখ: চোখ লাল হলে বা ঝাপসা দেখলে অবশ্যই ডাক্তার দেখান',

            // ═══ ENT / নাক-কান-গলা ═══
            'নাক-কান-গলা: গরম পানির দম নিন গলায় ব্যথা হলে',
            'নাক-কান-গলা: ঠান্ডা পানীয় এড়িয়ে চলুন',
            'নাক-কান-গলা: ফুঁপিয়ে নাক পরিষ্কার করুন, টিস্যু ব্যবহার করুন',
            'নাক-কান-গলা: কানে ব্যথা হলে তেল ঢালবেন না',
            'নাক-কান-গলা: গলায় তাৎক্ষণিক ব্যথা হলে গরম পানি ও লবণ পানি গার্গল করুন',

            // ═══ Kidney / কিডনি ═══
            'কিডনি: প্রচুর পানি পান করুন (প্রতিদিন ২-৩ লিটার)',
            'কিডনি: লবণ ও চিনি কম খান',
            'কিডনি: নিয়মিত কিডনি পরীক্ষা করুন',
            'কিডনি: বাইরের ওষুধ খাবেন না',
            'কিডনি: প্রোটিন সমৃদ্ধ খাবার পরিমিত পরিমাণে খান',

            // ═══ Liver / লিভার ═══
            'লিভার: মদ্যপান সম্পূর্ণ বন্ধ করুন',
            'লিভার: তৈলাক্ত ও ভাজাপোড়া খাবার এড়িয়ে চলুন',
            'লিভার: নিয়মিত লিভার ফাংশন টেস্ট করুন',
            'লিভার: পুষ্টিকর ও সুষম খাদ্য খান',
            'লিভার: হেপাটাইটিস-B এর টিকা নিন (যদি না থাকে)',

            // ═══ Heart / হৃদরোগ ═══
            'হৃদরোগ: তৈলাক্ত খাবার এড়িয়ে চলুন',
            'হৃদরোগ: নিয়মিত হাঁটুন',
            'হৃদরোগ: স্ট্রেস কমান',
            'হৃদরোগ: ধূমপান ও মদ্যপান বন্ধ করুন',
            'হৃদরোগ: ডাক্তারের পরামর্শ ছাড়া ওষুধ বন্ধ করবেন না',
            'হৃদরোগ: চেস্ট পেইন হলে অবশ্যই জরুরি বিভাগে যান',

            // ═══ Allergy / অ্যালার্জি ═══
            'অ্যালার্জি: অ্যালার্জেন থেকে দূরে থাকুন',
            'অ্যালার্জি: ডাক্তারের পরামর্শ ছাড়া এন্টিহিস্টামিন খাবেন না',
            'অ্যালার্জি: ঘরের পরিবেশ পরিষ্কার রাখুন',
            'অ্যালার্জি: ধুলাবালি থেকে সাবধান হন',
            'অ্যালার্জি: অ্যালার্জি টেস্ট করান',

            // ═══ Orthopedics / হাড়-জোড়া ═══
            'হাড়-জোড়া: ভারী বোঝা তোলা এড়িয়ে চলুন',
            'হাড়-জোড়া: সোজা হয়ে বসুন ও দাঁড়ান',
            'হাড়-জোড়া: ক্যালসিয়াম ও ভিটামিন-D সমৃদ্ধ খাবার খান',
            'হাড়-জোড়া: নিয়মিত হালকা ব্যায়াম করুন',
            'হাড়-জোড়া: একই অবস্থানে দীর্ঘক্ষণ বসে থাকবেন না',
            'হাড়-জোড়া: অর্থোপেডিক সিডি বা সাপোর্ট ব্যবহার করুন (ডাক্তারের পরামর্শে)',

            // ═══ General Instructions / সাধারণ নির্দেশনা ═══
            'ডাক্তারের পরামর্শ অনুযায়ী পরীক্ষা করান',
            'রিপোর্ট সহ ফলো-আপে আসুন',
            'পরের ভিজিটে পুরনো প্রেসক্রিপশন সাথে আনুন',
            'যেকোনো নতুন উপসর্গ দেখলে ডাক্তারকে জানান',
            'সার্জারির আগে খালি পেটে থাকুন (রাত ১২টা থেকে)',
            'পরীক্ষার আগে ৮-১০ ঘণ্টা খালি পেটে থাকুন (রক্ত পরীক্ষার জন্য)',
            'ইমেজিং পরীক্ষার আগে ডাক্তারের পরামর্শ অনুযায়ী প্রস্তুত হোন',
            'জরুরি অবস্থায় নিকটতম হাসপাতালে যান',
            'অনলাইনে বা ফোনে ডাক্তারের সাথে পরামর্শ করবেন না, সরাসরি দেখান',
        ];

        foreach ($advices as $name) {
            $existing = Advice::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first();
            if (!$existing) {
                Advice::create([
                    'name' => $name,
                    'status' => 'active',
                    'is_active' => true,
                ]);
            }
        }

        $this->command->info('✓ Advice seeded: ' . count($advices) . ' items');
    }

    private function seedClinicalSeals(): void
    {
        $seals = [
            'Dr. Smith Clinic',
            'City Medical Center',
            'HealthFirst Hospital',
            'MedCare Diagnostics',
            'Wellness Clinic',
            'Family Health Center',
            'Dr. Rahman\'s Chamber',
            'Modern Medical Center',
            'CarePoint Hospital',
            'MediLife Clinic',
            'Dr. Ahmed\'s Clinic',
            'Prime Health Center',
            'Apollo Clinic',
            'Sunrise Medical Center',
            'Green Health Hospital',
        ];

        foreach ($seals as $name) {
            ClinicalSeal::findByNameOrCreate($name);
        }

        $this->command->info('✓ Clinical Seals seeded: ' . count($seals) . ' items');
    }

    private function seedClinicalFeatures(): void
    {
        $features = [
            // General
            'Fever', 'Cough', 'Shortness of Breath', 'Chest Pain', 'Abdominal Pain',
            'Headache', 'Dizziness', 'Fatigue', 'Weakness', 'Weight Loss',
            'Weight Gain', 'Loss of Appetite', 'Nausea', 'Vomiting', 'Diarrhea',
            'Constipation', 'Insomnia', 'Night Sweats', 'Swelling', 'Palpitation',

            // Respiratory
            'Wheezing', 'Sputum Production', 'Hemoptysis', 'Pleuritic Chest Pain',
            'Tachypnea', 'Cyanosis', 'Nasal Congestion', 'Sore Throat',

            // Cardiovascular
            'Pedal Edema', 'Orthopnea', 'Paroxysmal Nocturnal Dyspnea',
            'Claudication', 'Varicose Veins', 'Raynaud\'s Phenomenon',

            // Gastrointestinal
            'Dysphagia', 'Heartburn', 'Bloating', 'Flatulence', 'Hematemesis',
            'Melena', 'Hematochezia', 'Jaundice', 'Pruritus', 'Ascites',

            // Neurological
            'Seizure', 'Syncope', 'Paralysis', 'Tremor', 'Numbness',
            'Tingling', 'Memory Loss', 'Confusion', 'Gait Disturbance',

            // Musculoskeletal
            'Joint Swelling', 'Joint Stiffness', 'Muscle Weakness',
            'Back Pain', 'Neck Pain', 'Reduced Range of Motion',

            // Dermatological
            'Rash', 'Erythema', 'Papule', 'Vesicle', 'Ulcer',
            'Scaling', 'Alopecia', 'Nail Changes',

            // ENT
            'Hearing Loss', 'Tinnitus', 'Epistaxis', 'Dysphonia',
            'Tonsillar Enlargement',

            // Eye
            'Visual Disturbance', 'Eye Redness', 'Eye Pain',
            'Proptosis', 'Ptosis',

            // Urological
            'Dysuria', 'Hematuria', 'Urinary Frequency', 'Urinary Urgency',
            'Urinary Incontinence', 'Hesitancy',

            // Obstetric
            'Morning Sickness', 'Abdominal Pain in Pregnancy',
            'Vaginal Bleeding', 'Breast Lump',

            // Pediatric
            'Poor Feeding', 'Delayed Milestones', 'Recurrent Infections',
            'Fever in Children', 'Abdominal Distension',

            // Psychiatric
            'Anxiety', 'Depression', 'Insomnia', 'Mood Changes',
            'Hallucination', 'Suicidal Ideation',

            // Endocrine
            'Polyuria', 'Polydipsia', 'Polyphagia', 'Heat Intolerance',
            'Cold Intolerance', 'Goiter', 'Tremor',

            // Allergic
            'Urticaria', 'Angioedema', 'Allergic Rhinitis',
            'Anaphylaxis', 'Drug Rash',

            // Others
            'Lymphadenopathy', 'Generalized Weakness', 'Malaise',
            'Anorexia', 'Thirst', 'Increased Appetite',
        ];

        foreach ($features as $name) {
            ClinicalFeature::findByNameOrCreate($name);
        }

        $this->command->info('✓ Clinical Features seeded: ' . count($features) . ' items');
    }

    private function seedFamilyHistories(): void
    {
        $histories = [
            // Chronic Diseases
            'Diabetes Mellitus', 'Hypertension', 'Ischemic Heart Disease',
            'Stroke', 'Asthma', 'COPD', 'Epilepsy', 'Migraine',
            'Bronchial Asthma', 'Tuberculosis', 'Cancer',
            'Chronic Kidney Disease', 'Chronic Liver Disease',
            'Hypothyroidism', 'Hyperthyroidism', 'Thyroid Disorder',
            'Rheumatoid Arthritis', 'Osteoarthritis', 'Gout',
            'Sickle Cell Disease', 'Thalassemia', 'Hemophilia',
            'Parkinson\'s Disease', 'Alzheimer\'s Disease',
            'Schizophrenia', 'Bipolar Disorder', 'Depression',
            'Anxiety Disorder', 'Obesity', 'Glaucoma', 'Cataract',
            'Macular Degeneration', 'Benign Prostatic Hyperplasia',
            'Polycystic Ovary Syndrome (PCOS)', 'Endometriosis',
            'Uterine Fibroids', 'Osteoporosis',

            // Allergic
            'Allergic Rhinitis', 'Food Allergy', 'Drug Allergy',
            'Eczema', 'Asthma (Allergic)', 'Hay Fever',

            // Autoimmune
            'Systemic Lupus Erythematosus (SLE)', 'Rheumatoid Arthritis',
            'Psoriasis', 'Vitiligo', 'Inflammatory Bowel Disease',
            'Celiac Disease', 'Type 1 Diabetes Mellitus',

            // Genetic / Congenital
            'Down Syndrome', 'Cystic Fibrosis', 'Muscular Dystrophy',
            'Turner Syndrome', 'Klinefelter Syndrome',
            'Spina Bifida', 'Congenital Heart Disease',
            'Sickle Cell Disease', 'Thalassemia Major',
            'Phenylketonuria (PKU)', 'Huntington\'s Disease',

            // Cancer
            'Breast Cancer', 'Colon Cancer', 'Lung Cancer',
            'Prostate Cancer', 'Ovarian Cancer', 'Stomach Cancer',
            'Leukemia', 'Lymphoma', 'Brain Tumor',
            'Cervical Cancer', 'Skin Cancer (Melanoma)',

            // Metabolic / Endocrine
            'Hyperlipidemia', 'Metabolic Syndrome',
            'Adrenal Insufficiency', 'Pituitary Disorder',
            'Porphyria', 'Hemochromatosis', 'Wilson\'s Disease',

            // Cardiovascular
            'Congestive Heart Failure', 'Cardiomyopathy',
            'Atrial Fibrillation', 'Rheumatic Heart Disease',
            'Coronary Artery Disease', 'Hypertrophic Cardiomyopathy',
            'Marfan Syndrome', 'Aortic Aneurysm',

            // Neurological
            'Epilepsy', 'Multiple Sclerosis', 'Myasthenia Gravis',
            'Motor Neuron Disease', 'Cerebral Palsy',
            'Intellectual Disability', 'Autism Spectrum Disorder',
            'ADHD', 'Tourette Syndrome',

            // Psychiatric
            'Schizophrenia', 'Bipolar Disorder', 'Major Depression',
            'Anxiety Disorder', 'Panic Disorder', 'OCD',
            'PTSD', 'Substance Abuse', 'Eating Disorder',

            // Renal
            'Chronic Kidney Disease', 'Kidney Stones',
            'Polycystic Kidney Disease', 'Nephrotic Syndrome',

            // Hepatic
            'Chronic Liver Disease', 'Hepatitis',
            'Cirrhosis', 'Gilbert Syndrome',

            // Hematological
            'Iron Deficiency Anemia', 'Pernicious Anemia',
            'Aplastic Anemia', 'Leukemia', 'Lymphoma',
            'Multiple Myeloma', 'Sickle Cell Disease',
            'Thalassemia', 'Hemophilia', 'Von Willebrand Disease',
            'G6PD Deficiency', 'Hereditary Spherocytosis',

            // Obstetric / Gynecological
            'Recurrent Miscarriage', 'Preeclampsia',
            'Gestational Diabetes', 'Polycystic Ovary Syndrome',
            'Premature Menopause', 'Endometriosis',

            // Others
            'Consanguineous Marriage', 'Twin Pregnancy',
            'Family History of Sudden Death',
            'Family History of Drug Reaction',
        ];

        foreach ($histories as $name) {
            FamilyHistory::findByNameOrCreate($name);
        }

        $this->command->info('✓ Family History seeded: ' . count($histories) . ' items');
    }

    private function seedMenstrualHistories(): void
    {
        $histories = [
            // Menstrual Pattern
            'Regular Menstrual Cycle', 'Irregular Menstrual Cycle',
            'Heavy Menstrual Bleeding (Menorrhagia)', 'Light Menstrual Bleeding',
            'Intermenstrual Bleeding', 'Postcoital Bleeding',
            'Prolonged Menstruation', 'Short Menstrual Cycle',
            'Long Menstrual Cycle', 'Absent Menstruation (Amenorrhea)',
            'Infrequent Menstruation (Oligomenorrhea)',

            // Pain
            'Dysmenorrhea (Painful Periods)', 'Severe Dysmenorrhea',
            'Premenstrual Syndrome (PMS)', 'Premenstrual Dysphoric Disorder (PMDD)',

            // Discharge
            'White Discharge (Leukorrhea)', 'Blood-Stained Discharge',
            'Foul-Smelling Discharge', 'Yellow/Green Discharge',

            // Menopause
            'Menopause', 'Perimenopause', 'Postmenopausal Bleeding',
            'Hot Flashes', 'Night Sweats',

            // Contraception
            'Oral Contraceptive Use', 'IUCD Use', 'Injectable Contraceptive',
            'Implant Use', 'Tubal Ligation', 'No Contraception',

            // Obstetric History
            'Primigravida', 'Multigravida', 'Grand Multipara',
            'Normal Menstrual Cycle Since Menarche',
            'Menarche at Age 12', 'Early Menarche', 'Late Menarche',

            // Associated Symptoms
            'Breast Tenderness Before Periods', 'Mood Swings Before Periods',
            'Bloating Before Periods', 'Headache Before Periods',
            'Fatigue During Periods', 'Backache During Periods',

            // Gynecological History
            'Ovarian Cyst', 'Uterine Fibroid', 'Endometriosis',
            'Pelvic Inflammatory Disease', 'Cervical Polyp',
            'Abnormal Pap Smear', 'HPV Infection',
            'Previous Gynecological Surgery',

            // Sexual Health
            'Sexually Active', 'Multiple Sexual Partners',
            'History of STI', 'Dyspareunia (Painful Intercourse)',
            'Vaginismus',

            // Fertility
            'Difficulty Conceiving', 'Infertility',
            'Previous IVF/Assisted Reproduction',
            'History of Ectopic Pregnancy', 'History of Molar Pregnancy',
            'Recurrent Pregnancy Loss',

            // Others
            'Nulliparous', 'Gravida', 'Para',
            'Abortions', 'Stillbirth', 'Live Births',
            'Lactating', 'Not Lactating',
        ];

        foreach ($histories as $name) {
            MenstrualHistory::findByNameOrCreate($name);
        }

        $this->command->info('✓ Menstrual History seeded: ' . count($histories) . ' items');
    }

    private function seedDrugHistories(): void
    {
        $drugs = [
            // Cardiovascular
            'Aspirin', 'Clopidogrel', 'Warfarin', 'Heparin', 'Enoxaparin',
            'Atorvastatin', 'Rosuvastatin', 'Simvastatin', 'Pravastatin',
            'Amlodipine', 'Nifedipine', 'Diltiazem', 'Verapamil',
            'Metoprolol', 'Atenolol', 'Bisoprolol', 'Carvedilol', 'Propranolol',
            'Enalapril', 'Ramipril', 'Lisinopril', 'Captopril',
            'Losartan', 'Valsartan', 'Telmisartan',
            'Furosemide', 'Spironolactone', 'Hydrochlorothiazide',
            'Digoxin', 'Amiodarone', 'Nitroglycerin', 'Isosorbide Mononitrate',

            // Diabetes
            'Metformin', 'Glimepiride', 'Gliclazide', 'Glipizide',
            'Sitagliptin', 'Vildagliptin', 'Empagliflozin',
            'Insulin (Regular)', 'Insulin (Glargine)', 'Insulin (Aspart)',
            'Pioglitazone', 'Acarbose',

            // Antibiotics
            'Amoxicillin', 'Azithromycin', 'Ciprofloxacin', 'Levofloxacin',
            'Doxycycline', 'Metronidazole', 'Cephalexin', 'Ceftriaxone',
            'Clarithromycin', 'Erythromycin', 'Piperacillin-Tazobactam',
            'Meropenem', 'Vancomycin', 'Linezolid', 'Fluconazole',

            // Pain & Anti-inflammatory
            'Paracetamol', 'Ibuprofen', 'Diclofenac', 'Naproxen',
            'Aceclofenac', 'Tramadol', 'Morphine', 'Codeine',
            'Pregabalin', 'Gabapentin', 'Carbamazepine',
            'Dexamethasone', 'Prednisolone', 'Methylprednisolone',
            'Hydrocortisone', 'Betamethasone',

            // Gastrointestinal
            'Omeprazole', 'Pantoprazole', 'Esomeprazole', 'Ranitidine',
            'Domperidone', 'Ondansetron', 'Loperamide', 'Lactulose',
            'Sucralfate', 'Misoprostol', 'Rifaximin',

            // Respiratory
            'Salbutamol (Inhaler)', 'Budesonide (Inhaler)',
            'Montelukast', 'Theophylline', 'Terbutaline',
            'Levosalbutamol', 'Formoterol', 'Tiotropium',
            'Ambroxol', 'Acetylcysteine', 'Bromhexine',

            // Thyroid
            'Levothyroxine', 'Methimazole', 'Carbimazole',
            'Liothyronine', 'Propylthiouracil',

            // Psychiatry
            'Sertraline', 'Fluoxetine', 'Escitalopram', 'Paroxetine',
            'Amitriptyline', 'Duloxetine', 'Venlafaxine',
            'Diazepam', 'Alprazolam', 'Lorazepam', 'Clonazepam',
            'Lithium', 'Valproic Acid', 'Quetiapine', 'Olanzapine',
            'Risperidone', 'Aripiprazole', 'Haloperidol',

            // Neurological
            'Phenytoin', 'Carbamazepine', 'Valproate',
            'Levetiracetam', 'Lamotrigine', 'Topiramate',
            'Levodopa/Carbidopa', 'Trihexyphenidyl',

            // Dermatological
            'Hydrocortisone Cream', 'Betamethasone Cream',
            'Miconazole Cream', 'Clotrimazole Cream',
            'Permethrin Cream', 'Retinoid Cream',
            'Salicylic Acid Ointment',

            // Ophthalmic
            'Timolol Eye Drops', 'Pilocarpine Eye Drops',
            'Moxifloxacin Eye Drops', 'Prednisolone Eye Drops',
            'Artificial Tears', 'Cyclopentolate Eye Drops',

            // ENT
            'Cetirizine', 'Loratadine', 'Levocetirizine',
            'Phenylephrine Nasal Drops', 'Oxymetazoline Nasal Drops',
            'Betamethasone Ear Drops', 'Ofloxacin Ear Drops',

            // Musculoskeletal
            'Calcium + Vitamin D', 'Alendronate', 'Risedronate',
            'Methylcobalamin', 'Vitamin D3', 'Glucosamine',

            // Vitamin & Supplements
            'Iron Supplement', 'Folic Acid', 'Vitamin B12',
            'Vitamin D3', 'Calcium', 'Multivitamin',
            'Omega-3 Fish Oil', 'Probiotics',

            // Contraceptive
            'Oral Contraceptive Pill', 'Emergency Contraceptive',
            'Depo-Provera (Injectable)', 'Copper IUCD',

            // Hormonal
            'Progesterone', 'Estrogen', 'Clomiphene',
            'Tamoxifen', 'Testosterone',

            // Others
            'Caffeine', 'Nicotine Replacement', 'Methotrexate',
            'Azathioprine', 'Mycophenolate', 'Cyclophosphamide',
            'Colchicine', 'Allopurinol', 'Febuxostat',
        ];

        foreach ($drugs as $name) {
            DrugHistory::findByNameOrCreate($name);
        }

        $this->command->info('✓ Drug History seeded: ' . count($drugs) . ' items');
    }

    private function seedOtNotes(): void
    {
        $procedures = [
            // General Surgery
            'Appendectomy', 'Cholecystectomy', 'Hernia Repair (Inguinal)',
            'Umbilical Hernia Repair', 'Incisional Hernia Repair',
            'Exploratory Laparotomy', 'Laparotomy',
            'Splenectomy', 'Hepatectomy', 'Pancreatic Surgery',
            'Bowel Resection', 'Colectomy', 'Colostomy',
            'Gastrostomy', 'Nephrectomy', 'Cystectomy',

            // Orthopedic
            'Total Hip Replacement', 'Total Knee Replacement',
            'Arthroscopy (Knee)', 'Arthroscopy (Shoulder)',
            'Fracture Fixation (ORIF)', 'Closed Reduction & Casting',
            'Spinal Fusion', 'Discectomy', 'Laminectomy',
            'Carpal Tunnel Release', 'Tendon Repair',
            'ACL Reconstruction', 'Meniscectomy',
            'Rotator Cuff Repair', 'Bunionectomy',
            'Achilles Tendon Repair', 'Bone Grafting',

            // Cardiovascular
            'Coronary Artery Bypass Graft (CABG)',
            'Angioplasty with Stent', 'Valve Replacement',
            'Pacemaker Implantation', 'ICD Implantation',
            'Peripheral Artery Bypass', 'Endarterectomy',
            'Aortic Aneurysm Repair',

            // Neurosurgery
            'Craniotomy', 'Epilepsy Surgery',
            'Shunt Placement (VP Shunt)', 'Brain Tumor Excision',
            'Spinal Cord Tumor Surgery', 'Decompressive Craniectomy',

            // ENT
            'Tonsillectomy', 'Adenoidectomy',
            'Septoplasty', 'Turbinate Reduction',
            'Mastoidectomy', 'Tympanoplasty',
            'Stapedectomy', 'Myringotomy with Grommet',
            'FESS (Functional Endoscopic Sinus Surgery)',
            'Parotidectomy', 'Thyroidectomy',
            'Neck Dissection', 'Laryngoscopy',
            'Microlaryngeal Surgery', 'Tracheostomy',

            // Urological
            'TURP (Transurethral Resection of Prostate)',
            'Ureteroscopy', 'PCNL (Percutaneous Nephrolithotomy)',
            'ESWL (Lithotripsy)', 'Circumcision',
            'Hydrocele Repair', 'Varicocelectomy',
            'Vasectomy', 'Cystoscopy',
            'Urethral Dilatation', 'Pyeloplasty',

            // Gynecological
            'Hysterectomy (Total)', 'Hysterectomy (Subtotal)',
            'Myomectomy', 'Ovarian Cystectomy',
            'Salpingectomy', 'Dilation & Curettage (D&C)',
            'Colposcopy', 'LEEP/LLETZ',
            'Tubal Ligation', 'Ectopic Pregnancy Surgery',
            'Caesarean Section', 'Episiotomy Repair',
            'Oophorectomy', 'Endometrial Ablation',

            // Ophthalmic
            'Cataract Surgery (Phacoemulsification)',
            'Cataract Surgery (SICS)', 'LASIK Surgery',
            'PRK Surgery', 'Glaucoma Surgery (Trabeculectomy)',
            'Vitrectomy', 'Retinal Detachment Surgery',
            'Pterygium Excision', 'Squint Surgery',
            'Ptosis Repair', 'Dacryocystorhinostomy (DCR)',

            // Plastic / Cosmetic
            'Liposuction', 'Abdominoplasty', 'Rhinoplasty',
            'Blepharoplasty', 'Facelift', 'Breast Augmentation',
            'Breast Reduction', 'Gynecomastia Surgery',
            'Burn Debridement', 'Skin Grafting',
            'Scar Revision', 'Z-plasty',

            // Minimal Access / Laparoscopic
            'Laparoscopic Cholecystectomy', 'Laparoscopic Appendectomy',
            'Laparoscopic Hernia Repair', 'Laparoscopic Nephrectomy',
            'Laparoscopic Fundoplication', 'Laparoscopic Colectomy',
            'Laparoscopic Splenectomy', 'Laparoscopic Ovarian Cystectomy',
            'Laparoscopic Tubal Ligation', 'Diagnostic Laparoscopy',

            // Pediatric
            'Circumcision (Neonatal)', 'Hydrocele Repair (Pediatric)',
            'Undescended Testis Repair (Orchidopexy)',
            'Hernia Repair (Pediatric)',
            'Pyloric Stenosis Surgery',
            'Imperforate Anus Repair',

            // Oncological
            'Mastectomy', 'Lumpectomy', 'Lymph Node Dissection',
            'Tumor Excision', 'Wide Local Excision',
            'Hemithyroidectomy', 'Total Thyroidectomy',
            'Radical Nephrectomy', 'Radical Prostatectomy',
            'Cystoprostatectomy', 'Pelvic Exenteration',

            // Emergency / Trauma
            'Wound Debridement', 'Suturing of Wounds',
            'Abscess Incision & Drainage',
            'Chest Tube Insertion (Thoracostomy)',
            'Emergency Thoracotomy', 'Damage Control Surgery',
            'External Fixator Application',

            // Vascular
            'Varicose Vein Surgery', 'AV Fistula Creation',
            'Carotid Endarterectomy', 'Embolectomy',
            'Bypass Grafting (Peripheral)',

            // Endocrine
            'Thyroidectomy', 'Parathyroidectomy',
            'Adrenalectomy', 'Adrenal Gland Surgery',

            // Others
            'Biopsy', 'FNAC', 'Excision Biopsy',
            'Incision & Drainage', 'Debridement',
            'Endoscopy (Diagnostic)', 'Colonoscopy (Diagnostic)',
            'Bronchoscopy', 'ERCP', 'Endoscopic Ultrasound',
            'PEG Tube Placement', 'J-Tube Placement',
        ];

        foreach ($procedures as $name) {
            OtNote::findByNameOrCreate($name);
        }

        $this->command->info('✓ OT Note / Procedure Done seeded: ' . count($procedures) . ' items');
    }

    private function seedAnesthesiaRecords(): void
    {
        $records = [
            // Types of Anesthesia
            'General Anesthesia', 'Local Anesthesia', 'Regional Anesthesia',
            'Spinal Anesthesia', 'Epidural Anesthesia',
            'Combined Spinal-Epidural', 'Regional Nerve Block',
            'Bier\'s Block (Intravenous Regional)', 'Conscious Sedation',
            'Monitored Anesthesia Care (MAC)', 'Topical Anesthesia',
            'Plexus Block (Brachial/Psoas)', 'TAP Block',
            'Field Block', 'Digital Block',

            // Techniques
            'Endotracheal Intubation', 'LMA (Laryngeal Mask Airway)',
            'Fibreoptic Intubation', 'Video Laryngoscopy',
            'RSI (Rapid Sequence Induction)', 'Mask Anesthesia',
            'Total Intravenous Anesthesia (TIVA)',
            'Balanced Anesthesia', 'Gas Anesthesia',
            'Dissociative Anesthesia (Ketamine)',

            // Drugs - Induction
            'Propofol', 'Thiopentone', 'Ketamine',
            'Etomidate', 'Midazolam', 'Diazepam',
            'Fentanyl', 'Remifentanil', 'Sufentanil',

            // Drugs - Maintenance
            'Sevoflurane', 'Desflurane', 'Isoflurane',
            'Nitrous Oxide', 'Nitrous Oxide + Oxygen',
            'Halothane',

            // Drugs - Muscle Relaxants
            'Succinylcholine (Suxamethonium)',
            'Atracurium', 'Cisatracurium',
            'Rocuronium', 'Vecuronium',
            'Pancuronium', 'Mivacurium',
            'Sugammadex (Reversal)', 'Neostigmine (Reversal)',

            // Regional Anesthesia Drugs
            'Bupivacaine', 'Lidocaine', 'Ropivacaine',
            'Levobupivacaine', 'Chlorprocaine',
            'Bupivacaine with Dextrose (Hyperbaric)',
            'Bupivacaine without Dextrose (Isobaric)',

            // Monitoring
            'Standard ASA Monitoring', 'Invasive BP Monitoring',
            'Central Venous Catheter', 'Arterial Line',
            'Pulmonary Artery Catheter', 'TEE (Transesophageal Echocardiography)',
            'BIS Monitoring', 'Nerve Stimulator',
            'Capnography', 'Pulse Oximetry',

            // Airway Management
            'Difficult Airway', 'Awake Intubation',
            'Emergency Surgical Airway', 'Cricothyroidotomy',
            'Tracheostomy', 'Bronchoscopy-Guided Intubation',

            // Patient Conditions
            'ASA Grade I', 'ASA Grade II', 'ASA Grade III',
            'ASA Grade IV', 'ASA Grade V',
            'Full Stomach', 'Difficult Airway Expected',
            'Cardiac Patient', 'Diabetic Patient',
            'Pediatric Patient', 'Geriatric Patient',
            'Pregnant Patient', 'Obese Patient',

            // Complications
            'Intraoperative Hypotension', 'Intraoperative Hypertension',
            'Bradycardia', 'Tachycardia', 'Arrhythmia',
            'Laryngospasm', 'Bronchospasm',
            'Malignant Hyperthermia', 'Awareness',
            'Nausea & Vomiting (PONV)', 'Post-spinal Headache',
            'Epidural Hematoma', 'Epidural Abscess',
            'Nerve Injury', 'Anaphylaxis',

            // Post-Anesthesia
            'PACU (Post-Anesthesia Care Unit)',
            'Fast-Track Recovery', 'Enhanced Recovery After Surgery (ERAS)',
            'Postoperative Pain Management',
            'Patient-Controlled Analgesia (PCA)',
            'Epidural Analgesia', 'Nerve Block Analgesia',
            'Multimodal Analgesia',
            'Postoperative Nausea/Vomiting Management',
            'Discharged from PACU Stable',
        ];

        foreach ($records as $name) {
            AnesthesiaRecord::findByNameOrCreate($name);
        }

        $this->command->info('✓ Anesthesia Records seeded: ' . count($records) . ' items');
    }
}
