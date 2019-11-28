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
            'friendly_name' => 'Administrador',
            'description' => 'Administra o sistema (root)'
        ]);

        $role = Role::create([
            'name' => 'teacher',
            'friendly_name' => 'Professor',
            'description' => 'Professor de uma disciplina técnica'
        ]);

        $role = Role::create([
            'name' => 'coordinator',
            'friendly_name' => 'Coordenador',
            'description' => 'Professores coordenadores'
        ]);

        $role = Role::create([
            'name' => 'company',
            'friendly_name' => 'Empresa',
            'description' => 'Empresas conveniadas com o colégio'
        ]);

        $role = Role::create([
            'name' => 'student',
            'friendly_name' => 'Aluno',
            'description' => 'Alunos do NSac'
        ]);
    }
}
