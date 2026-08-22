<?php

namespace Database\Seeders;

use App\Models\InvestigationGroup;
use App\Models\InvestigationGroupParameter;
use Illuminate\Database\Seeder;

class InvestigationGroupSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            [
                'name' => 'CBC', 'name_bn' => 'সিবিসি', 'sort_order' => 1,
                'parameters' => [
                    ['parameter_name' => 'Hb', 'parameter_name_bn' => 'হিমোগ্লোবিন', 'unit' => 'g/dL', 'reference_range' => '13.5-17.5 (M) / 12.0-15.5 (F)', 'sort_order' => 1],
                    ['parameter_name' => 'RBC', 'parameter_name_bn' => 'আরবিসি', 'unit' => 'million/μL', 'reference_range' => '4.5-5.5', 'sort_order' => 2],
                    ['parameter_name' => 'WBC', 'parameter_name_bn' => 'ডব্লিউবিসি', 'unit' => '/μL', 'reference_range' => '4,000-11,000', 'sort_order' => 3],
                    ['parameter_name' => 'Platelets', 'parameter_name_bn' => 'প্লেটলেট', 'unit' => '/μL', 'reference_range' => '150,000-450,000', 'sort_order' => 4],
                    ['parameter_name' => 'PCV/HCT', 'parameter_name_bn' => 'পিসিভি/এইচসিটি', 'unit' => '%', 'reference_range' => '40-50 (M) / 36-46 (F)', 'sort_order' => 5],
                    ['parameter_name' => 'MCV', 'unit' => 'fL', 'reference_range' => '80-100', 'sort_order' => 6],
                    ['parameter_name' => 'MCH', 'unit' => 'pg', 'reference_range' => '27-34', 'sort_order' => 7],
                    ['parameter_name' => 'MCHC', 'unit' => 'g/dL', 'reference_range' => '32-36', 'sort_order' => 8],
                    ['parameter_name' => 'Neutrophils', 'parameter_name_bn' => 'নিউট্রোফিল', 'unit' => '%', 'reference_range' => '40-80', 'sort_order' => 9],
                    ['parameter_name' => 'Lymphocytes', 'parameter_name_bn' => 'লিম্ফোসাইট', 'unit' => '%', 'reference_range' => '20-40', 'sort_order' => 10],
                    ['parameter_name' => 'Eosinophils', 'parameter_name_bn' => 'ইওসিনোফিল', 'unit' => '%', 'reference_range' => '1-6', 'sort_order' => 11],
                    ['parameter_name' => 'Monocytes', 'parameter_name_bn' => 'মনোসাইট', 'unit' => '%', 'reference_range' => '2-8', 'sort_order' => 12],
                    ['parameter_name' => 'Basophils', 'parameter_name_bn' => 'বেসোফিল', 'unit' => '%', 'reference_range' => '0-1', 'sort_order' => 13],
                ],
            ],
            [
                'name' => 'Blood Sugar', 'name_bn' => 'ব্লাড সুগার', 'sort_order' => 2,
                'parameters' => [
                    ['parameter_name' => 'Fasting', 'parameter_name_bn' => 'রোজা', 'unit' => 'mg/dL', 'reference_range' => '70-110', 'sort_order' => 1],
                    ['parameter_name' => 'Post Prandial', 'parameter_name_bn' => 'পোস্ট প্র্যান্ডিয়াল', 'unit' => 'mg/dL', 'reference_range' => '<140', 'sort_order' => 2],
                    ['parameter_name' => 'Random', 'parameter_name_bn' => 'র্যান্ডম', 'unit' => 'mg/dL', 'reference_range' => '<200', 'sort_order' => 3],
                ],
            ],
            [
                'name' => 'Lipid Profile', 'name_bn' => 'লিপিড প্রোফাইল', 'sort_order' => 3,
                'parameters' => [
                    ['parameter_name' => 'Total Cholesterol', 'parameter_name_bn' => 'মোট কোলেস্টেরল', 'unit' => 'mg/dL', 'reference_range' => '<200', 'sort_order' => 1],
                    ['parameter_name' => 'HDL', 'unit' => 'mg/dL', 'reference_range' => '>40 (M) / >50 (F)', 'sort_order' => 2],
                    ['parameter_name' => 'LDL', 'unit' => 'mg/dL', 'reference_range' => '<130', 'sort_order' => 3],
                    ['parameter_name' => 'Triglycerides', 'parameter_name_bn' => 'ট্রাইগ্লিসারাইড', 'unit' => 'mg/dL', 'reference_range' => '<150', 'sort_order' => 4],
                    ['parameter_name' => 'VLDL', 'unit' => 'mg/dL', 'reference_range' => '5-40', 'sort_order' => 5],
                ],
            ],
            [
                'name' => 'Liver Function', 'name_bn' => 'লিভার ফাংশন', 'sort_order' => 4,
                'parameters' => [
                    ['parameter_name' => 'Total Bilirubin', 'parameter_name_bn' => 'মোট বিলিরুবিন', 'unit' => 'mg/dL', 'reference_range' => '0.1-1.2', 'sort_order' => 1],
                    ['parameter_name' => 'Direct Bilirubin', 'parameter_name_bn' => 'ডাইরেক্ট বিলিরুবিন', 'unit' => 'mg/dL', 'reference_range' => '0.1-0.4', 'sort_order' => 2],
                    ['parameter_name' => 'Indirect Bilirubin', 'parameter_name_bn' => 'ইনডাইরেক্ট বিলিরুবিন', 'unit' => 'mg/dL', 'reference_range' => '0.2-0.8', 'sort_order' => 3],
                    ['parameter_name' => 'AST (SGOT)', 'unit' => 'U/L', 'reference_range' => '10-40', 'sort_order' => 4],
                    ['parameter_name' => 'ALT (SGPT)', 'unit' => 'U/L', 'reference_range' => '10-40', 'sort_order' => 5],
                    ['parameter_name' => 'ALP', 'unit' => 'U/L', 'reference_range' => '44-147', 'sort_order' => 6],
                    ['parameter_name' => 'Total Protein', 'parameter_name_bn' => 'মোট প্রোটিন', 'unit' => 'g/dL', 'reference_range' => '6.0-8.3', 'sort_order' => 7],
                    ['parameter_name' => 'Albumin', 'parameter_name_bn' => 'অ্যালবুমিন', 'unit' => 'g/dL', 'reference_range' => '3.5-5.0', 'sort_order' => 8],
                    ['parameter_name' => 'Globulin', 'parameter_name_bn' => 'গ্লোবুলিন', 'unit' => 'g/dL', 'reference_range' => '2.0-3.5', 'sort_order' => 9],
                    ['parameter_name' => 'A/G Ratio', 'unit' => '', 'reference_range' => '1.0-2.0', 'sort_order' => 10],
                ],
            ],
            [
                'name' => 'Kidney Function', 'name_bn' => 'কিডনি ফাংশন', 'sort_order' => 5,
                'parameters' => [
                    ['parameter_name' => 'Blood Urea', 'parameter_name_bn' => 'ব্লাড ইউরিয়া', 'unit' => 'mg/dL', 'reference_range' => '10-50', 'sort_order' => 1],
                    ['parameter_name' => 'Serum Creatinine', 'parameter_name_bn' => 'সিরাম ক্রিয়েটিনিন', 'unit' => 'mg/dL', 'reference_range' => '0.6-1.2', 'sort_order' => 2],
                    ['parameter_name' => 'BUN', 'unit' => 'mg/dL', 'reference_range' => '7-20', 'sort_order' => 3],
                    ['parameter_name' => 'Uric Acid', 'parameter_name_bn' => 'ইউরিক অ্যাসিড', 'unit' => 'mg/dL', 'reference_range' => '3.5-7.2', 'sort_order' => 4],
                    ['parameter_name' => 'Sodium', 'parameter_name_bn' => 'সোডিয়াম', 'unit' => 'mEq/L', 'reference_range' => '136-145', 'sort_order' => 5],
                    ['parameter_name' => 'Potassium', 'parameter_name_bn' => 'পটাশিয়াম', 'unit' => 'mEq/L', 'reference_range' => '3.5-5.1', 'sort_order' => 6],
                    ['parameter_name' => 'Chloride', 'parameter_name_bn' => 'ক্লোরাইড', 'unit' => 'mEq/L', 'reference_range' => '98-106', 'sort_order' => 7],
                ],
            ],
            [
                'name' => 'Thyroid Profile', 'name_bn' => 'থাইরয়েড প্রোফাইল', 'sort_order' => 6,
                'parameters' => [
                    ['parameter_name' => 'T3', 'unit' => 'ng/mL', 'reference_range' => '0.8-2.0', 'sort_order' => 1],
                    ['parameter_name' => 'T4', 'unit' => 'μg/dL', 'reference_range' => '5.0-12.0', 'sort_order' => 2],
                    ['parameter_name' => 'TSH', 'unit' => 'μIU/mL', 'reference_range' => '0.4-4.0', 'sort_order' => 3],
                ],
            ],
            [
                'name' => 'HbA1c', 'sort_order' => 7,
                'parameters' => [
                    ['parameter_name' => 'HbA1c', 'unit' => '%', 'reference_range' => '<5.7 (Normal)', 'sort_order' => 1],
                ],
            ],
            [
                'name' => 'Vitamin D', 'name_bn' => 'ভিটামিন ডি', 'sort_order' => 8,
                'parameters' => [
                    ['parameter_name' => 'Vitamin D (25-OH)', 'parameter_name_bn' => 'ভিটামিন ডি (২৫-ওএইচ)', 'unit' => 'ng/mL', 'reference_range' => '30-100', 'sort_order' => 1],
                ],
            ],
            [
                'name' => 'Vitamin B12', 'name_bn' => 'ভিটামিন বি১২', 'sort_order' => 9,
                'parameters' => [
                    ['parameter_name' => 'Vitamin B12', 'parameter_name_bn' => 'ভিটামিন বি১২', 'unit' => 'pg/mL', 'reference_range' => '200-900', 'sort_order' => 1],
                ],
            ],
            [
                'name' => 'Iron Profile', 'name_bn' => 'আয়রন প্রোফাইল', 'sort_order' => 10,
                'parameters' => [
                    ['parameter_name' => 'Serum Iron', 'parameter_name_bn' => 'সিরাম আয়রন', 'unit' => 'μg/dL', 'reference_range' => '50-170', 'sort_order' => 1],
                    ['parameter_name' => 'TIBC', 'unit' => 'μg/dL', 'reference_range' => '250-450', 'sort_order' => 2],
                    ['parameter_name' => 'Iron Saturation', 'parameter_name_bn' => 'আয়রন স্যাচুরেশন', 'unit' => '%', 'reference_range' => '20-50', 'sort_order' => 3],
                    ['parameter_name' => 'Ferritin', 'parameter_name_bn' => 'ফেরিটিন', 'unit' => 'ng/mL', 'reference_range' => '20-300 (M) / 15-150 (F)', 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'CRP', 'sort_order' => 11,
                'parameters' => [
                    ['parameter_name' => 'CRP', 'unit' => 'mg/L', 'reference_range' => '<5', 'sort_order' => 1],
                ],
            ],
            [
                'name' => 'ESR', 'sort_order' => 12,
                'parameters' => [
                    ['parameter_name' => 'ESR', 'unit' => 'mm/hr', 'reference_range' => '0-15 (M) / 0-20 (F)', 'sort_order' => 1],
                ],
            ],
            [
                'name' => 'Urinalysis', 'name_bn' => 'ইউরিনালাইসিস', 'sort_order' => 13,
                'parameters' => [
                    ['parameter_name' => 'Color', 'parameter_name_bn' => 'রঙ', 'unit' => '', 'reference_range' => 'Pale yellow', 'sort_order' => 1],
                    ['parameter_name' => 'Appearance', 'parameter_name_bn' => 'চেহারা', 'unit' => '', 'reference_range' => 'Clear', 'sort_order' => 2],
                    ['parameter_name' => 'pH', 'unit' => '', 'reference_range' => '4.5-8.0', 'sort_order' => 3],
                    ['parameter_name' => 'Specific Gravity', 'unit' => '', 'reference_range' => '1.005-1.030', 'sort_order' => 4],
                    ['parameter_name' => 'Protein', 'parameter_name_bn' => 'প্রোটিন', 'unit' => '', 'reference_range' => 'Negative', 'sort_order' => 5],
                    ['parameter_name' => 'Glucose', 'parameter_name_bn' => 'গ্লুকোজ', 'unit' => '', 'reference_range' => 'Negative', 'sort_order' => 6],
                    ['parameter_name' => 'Ketones', 'parameter_name_bn' => 'কিটোন', 'unit' => '', 'reference_range' => 'Negative', 'sort_order' => 7],
                    ['parameter_name' => 'Blood', 'parameter_name_bn' => 'রক্ত', 'unit' => '', 'reference_range' => 'Negative', 'sort_order' => 8],
                    ['parameter_name' => 'Leukocytes', 'parameter_name_bn' => 'লিউকোসাইট', 'unit' => '', 'reference_range' => 'Negative', 'sort_order' => 9],
                    ['parameter_name' => 'Nitrite', 'unit' => '', 'reference_range' => 'Negative', 'sort_order' => 10],
                    ['parameter_name' => 'Urobilinogen', 'unit' => '', 'reference_range' => '0.1-1.0', 'sort_order' => 11],
                    ['parameter_name' => 'Bilirubin', 'parameter_name_bn' => 'বিলিরুবিন', 'unit' => '', 'reference_range' => 'Negative', 'sort_order' => 12],
                ],
            ],
            [
                'name' => 'ECG', 'sort_order' => 14,
                'parameters' => [
                    ['parameter_name' => 'ECG', 'unit' => '', 'reference_range' => '-', 'sort_order' => 1],
                ],
            ],
            [
                'name' => 'X-Ray', 'sort_order' => 15,
                'parameters' => [
                    ['parameter_name' => 'X-Ray', 'unit' => '', 'reference_range' => '-', 'sort_order' => 1],
                ],
            ],
            [
                'name' => 'Ultrasound', 'sort_order' => 16,
                'parameters' => [
                    ['parameter_name' => 'Ultrasound', 'unit' => '', 'reference_range' => '-', 'sort_order' => 1],
                ],
            ],
            [
                'name' => 'Creatinine', 'sort_order' => 17,
                'parameters' => [
                    ['parameter_name' => 'Creatinine', 'unit' => 'mg/dL', 'reference_range' => '0.6-1.2', 'sort_order' => 1],
                ],
            ],
        ];

        foreach ($groups as $groupData) {
            $params = $groupData['parameters'];
            unset($groupData['parameters']);
            $group = InvestigationGroup::create($groupData);
            foreach ($params as $param) {
                $group->parameters()->create($param);
            }
        }
    }
}
