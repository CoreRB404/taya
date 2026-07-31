<?php

namespace Database\Seeders;

use App\Models\PenaltyReference;
use Illuminate\Database\Seeder;

class PenaltyReferenceSeeder extends Seeder
{
    public function run(): void
    {
        $penalties = [
            ['rpc_code' => 'Art. 308', 'charge_name' => 'Theft', 'max_penalty_years' => 8.00, 'max_penalty_months' => null, 'law_source' => 'RPC'],
            ['rpc_code' => 'Art. 315', 'charge_name' => 'Estafa (Swindling)', 'max_penalty_years' => 6.00, 'max_penalty_months' => null, 'law_source' => 'RPC'],
            ['rpc_code' => 'Art. 249', 'charge_name' => 'Homicide', 'max_penalty_years' => 20.00, 'max_penalty_months' => null, 'law_source' => 'RPC'],
            ['rpc_code' => 'Art. 266-A', 'charge_name' => 'Rape', 'max_penalty_years' => 40.00, 'max_penalty_months' => null, 'law_source' => 'RPC'],
            ['rpc_code' => 'Art. 294', 'charge_name' => 'Robbery', 'max_penalty_years' => 12.00, 'max_penalty_months' => null, 'law_source' => 'RPC'],
            ['rpc_code' => 'RA 9165 §5', 'charge_name' => 'Illegal Drug Sale (RA 9165)', 'max_penalty_years' => 12.00, 'max_penalty_months' => null, 'law_source' => 'RA'],
            ['rpc_code' => 'RA 10883 §3', 'charge_name' => 'Carnapping', 'max_penalty_years' => 20.00, 'max_penalty_months' => null, 'law_source' => 'RA'],
            ['rpc_code' => 'Art. 248', 'charge_name' => 'Murder', 'max_penalty_years' => 40.00, 'max_penalty_months' => null, 'law_source' => 'RPC'],
            ['rpc_code' => 'Art. 263', 'charge_name' => 'Serious Physical Injuries', 'max_penalty_years' => 6.00, 'max_penalty_months' => null, 'law_source' => 'RPC'],
            ['rpc_code' => 'Art. 249/6', 'charge_name' => 'Frustrated Homicide', 'max_penalty_years' => 10.00, 'max_penalty_months' => null, 'law_source' => 'RPC'],
            ['rpc_code' => 'Art. 267', 'charge_name' => 'Kidnapping and Serious Illegal Detention', 'max_penalty_years' => 40.00, 'max_penalty_months' => null, 'law_source' => 'RPC'],
            ['rpc_code' => 'Art. 335', 'charge_name' => 'Acts of Lasciviousness', 'max_penalty_years' => 6.00, 'max_penalty_months' => null, 'law_source' => 'RPC'],
            ['rpc_code' => 'RA 9165 §11', 'charge_name' => 'Illegal Drug Possession (RA 9165)', 'max_penalty_years' => 12.00, 'max_penalty_months' => null, 'law_source' => 'RA'],
            ['rpc_code' => 'RA 10591 §28', 'charge_name' => 'Illegal Possession of Firearms', 'max_penalty_years' => 12.00, 'max_penalty_months' => null, 'law_source' => 'RA'],
            ['rpc_code' => 'Art. 262', 'charge_name' => 'Mutilation', 'max_penalty_years' => 12.00, 'max_penalty_months' => null, 'law_source' => 'RPC'],
            ['rpc_code' => 'Art. 310', 'charge_name' => 'Qualified Theft', 'max_penalty_years' => 20.00, 'max_penalty_months' => null, 'law_source' => 'RPC'],
            ['rpc_code' => 'Art. 264', 'charge_name' => 'Less Serious Physical Injuries', 'max_penalty_years' => 1.00, 'max_penalty_months' => null, 'law_source' => 'RPC'],
            ['rpc_code' => 'Art. 293', 'charge_name' => 'Robbery with Violence', 'max_penalty_years' => 20.00, 'max_penalty_months' => null, 'law_source' => 'RPC'],
            ['rpc_code' => 'PD 1866 §1', 'charge_name' => 'Illegal Possession of Explosives', 'max_penalty_years' => 12.00, 'max_penalty_months' => null, 'law_source' => 'PD'],
            ['rpc_code' => 'Art. 246', 'charge_name' => 'Parricide', 'max_penalty_years' => 40.00, 'max_penalty_months' => null, 'law_source' => 'RPC'],
            ['rpc_code' => 'RA 7610 §5', 'charge_name' => 'Child Abuse', 'max_penalty_years' => 8.00, 'max_penalty_months' => null, 'law_source' => 'RA'],
            ['rpc_code' => 'Art. 247', 'charge_name' => 'Death Under Exceptional Circumstances', 'max_penalty_years' => 6.00, 'max_penalty_months' => null, 'law_source' => 'RPC'],
        ];

        foreach ($penalties as $penalty) {
            PenaltyReference::create([
                ...$penalty,
                'last_validated' => now(),
            ]);
        }
    }
}
