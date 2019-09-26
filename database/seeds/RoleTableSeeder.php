<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleTableSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $role = Role::create([
            'name' => 'admin',
            'friendlyName' => 'Administrador',
            'description' => 'Administra o sistema (root)'
        ]);

        $role = Role::create([
            'name' => 'teacher',
            'friendlyName' => 'Professor',
            'description' => 'Professor de uma disciplina técnica'
        ]);

        $role = Role::create([
            'name' => 'coordinator',
            'friendlyName' => 'Coordenadore',
            'description' => 'Professores coordenadores'
        ]);

        $role = Role::create([
            'name' => 'company',
            'friendlyName' => 'Empresa',
            'description' => 'Empresas conveniadas com o colégio'
        ]);

        $role = Role::create([
            'name' => 'student',
            'friendlyName' => 'Aluno',
            'description' => 'Alunos do NSac'
        ]);
    }
}
