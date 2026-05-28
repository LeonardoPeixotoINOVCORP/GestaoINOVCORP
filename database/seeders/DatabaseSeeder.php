<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PlanSeeder::class,          
            PermissionSeeder::class,    
            TenantSeeder::class,        
            UserSeeder::class,          
            PaisSeeder::class,          
            ContactoFuncaoSeeder::class,
            EmpresaSeeder::class,       
            ContaBancariaSeeder::class, 
            ArtigoSeeder::class,        
            EntidadeSeeder::class,      
            ContactoSeeder::class,     
            PropostaSeeder::class,   
            EncomendaSeeder::class,    
            FaturaFornecedorSeeder::class, 
            CalendarioSeeder::class,    
        ]);
    }
}