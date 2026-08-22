<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DoctorPortalSeeder extends Seeder
{
    public function run(): void
    {
        $doctors = User::role('doctor')->get();

        foreach ($doctors as $doctor) {
            // Create 5-8 patients per doctor
            $patientCount = rand(5, 8);
            $patients = [];

            for ($i = 1; $i <= $patientCount; $i++) {
                $patient = Patient::create([
                    'doctor_id' => $doctor->id,
                    'name' => fake()->name(),
                    'email' => 'patient' . $doctor->id . '_' . $i . '@example.com',
                    'phone' => '01' . rand(700000000, 999999999),
                    'date_of_birth' => Carbon::now()->subYears(rand(18, 80))->subDays(rand(0, 365)),
                    'gender' => ['Male', 'Female', 'Other'][rand(0, 2)],
                    'address' => fake()->address(),
                    'medical_history' => rand(0, 1) ? fake()->sentence(10) : null,
                    'created_at' => Carbon::now()->subDays(rand(0, 90)),
                ]);

                $patients[] = $patient;
            }

            // Create appointments for each patient
            foreach ($patients as $patient) {
                // 1 past appointment
                Appointment::create([
                    'doctor_id' => $doctor->id,
                    'patient_id' => $patient->id,
                    'appointment_date' => Carbon::now()->subDays(rand(1, 30))->setTime(rand(9, 17), rand(0, 3) * 15),
                    'status' => 'completed',
                    'reason' => fake()->sentence(4),
                ]);

                // 1 future appointment (for some patients)
                if (rand(0, 1)) {
                    Appointment::create([
                        'doctor_id' => $doctor->id,
                        'patient_id' => $patient->id,
                        'appointment_date' => Carbon::now()->addDays(rand(1, 14))->setTime(rand(9, 17), rand(0, 3) * 15),
                        'status' => 'scheduled',
                        'reason' => fake()->sentence(4),
                    ]);
                }
            }

            // Create prescriptions for some patients
            $medicines = Medicine::where('is_global', true)->where('status', 'active')->get();

            foreach (array_slice($patients, 0, rand(3, 5)) as $patient) {
                $rx = Prescription::create([
                    'doctor_id' => $doctor->id,
                    'patient_id' => $patient->id,
                    'prescription_number' => 'RX-' . date('Ymd') . '-' . str_pad(Prescription::max('id') + 1, 4, '0', STR_PAD_LEFT),
                    'diagnosis' => fake()->sentence(6),
                    'notes' => rand(0, 1) ? fake()->sentence(8) : null,
                    'follow_up_instructions' => rand(0, 1) ? fake()->sentence(5) : null,
                    'follow_up_date' => rand(0, 1) ? Carbon::now()->addDays(rand(7, 30)) : null,
                    'status' => 'active',
                    'created_at' => Carbon::now()->subDays(rand(0, 60)),
                ]);

                // Add 2-4 medicine items per prescription
                $itemCount = rand(2, 4);
                $usedMedicines = [];

                for ($j = 0; $j < $itemCount; $j++) {
                    $med = $medicines->random();
                    if (in_array($med->id, $usedMedicines)) continue;
                    $usedMedicines[] = $med->id;

                    PrescriptionItem::create([
                        'prescription_id' => $rx->id,
                        'medicine_id' => $med->id,
                        'medicine_name' => $med->name,
                        'dosage' => $med->strength ?? 'As directed',
                        'frequency' => ['1+0+0', '0+0+1', '1+1+1', '1+0+1', '0+1+0'][rand(0, 4)],
                        'duration' => rand(3, 14) . ' days',
                        'instructions' => rand(0, 1) ? fake()->sentence(6) : null,
                        'route' => ['oral', 'topical', 'injection', 'inhalation'][rand(0, 3)],
                        'when_to_take' => ['before_food', 'after_food', 'morning', 'evening', 'night'][rand(0, 4)],
                        'quantity' => rand(10, 60),
                        'refills' => rand(0, 3),
                    ]);
                }

                // Add advice tags
                $advicePool = ['Drink plenty of water', 'Take adequate rest', 'Avoid oily food', 'Exercise regularly', 'Avoid smoking', 'Limit alcohol intake', 'Take medicine after meal', 'Take medicine before meal', 'Monitor blood pressure', 'Monitor blood sugar'];
                $adviceCount = rand(0, 3);
                $usedAdvice = [];
                for ($a = 0; $a < $adviceCount; $a++) {
                    $aText = $advicePool[array_rand($advicePool)];
                    if (in_array($aText, $usedAdvice)) continue;
                    $usedAdvice[] = $aText;
                    $rx->advice()->create([
                        'advice' => $aText,
                        'sort_order' => $a,
                    ]);
                }
            }
        }

        $this->command->info('Seeded patients, appointments, and prescriptions for all doctors.');
    }
}
