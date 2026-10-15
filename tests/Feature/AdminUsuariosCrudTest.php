<?php

namespace Tests\Feature;

use App\Modules\Usuarios\Models\Usuario;
use App\Support\Enums\RolEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminUsuariosCrudTest extends TestCase
{
    use RefreshDatabase;

    private function crearAdmin(array $datos = []): Usuario
    {
        return Usuario::factory()->administrador()->create($datos);
    }

    #[Test]
    public function un_jurado_no_puede_acceder_a_la_gestion_de_usuarios(): void
    {
        $jurado = Usuario::factory()->create(['rol' => RolEnum::JURADO]);

        $this->actingAs($jurado)
            ->get(route('admin.usuarios.index'))
            ->assertForbidden();

        $this->actingAs($jurado)
            ->post(route('admin.usuarios.store'), [])
            ->assertForbidden();
    }

    #[Test]
    public function un_admin_puede_listar_usuarios(): void
    {
        $admin = $this->crearAdmin();
        Usuario::factory()->count(3)->create(['rol' => RolEnum::JURADO]);

        $this->actingAs($admin)
            ->get(route('admin.usuarios.index'))
            ->assertOk()
            ->assertSee($admin->email);
    }

    #[Test]
    public function un_admin_puede_crear_un_usuario_verificado(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)->post(route('admin.usuarios.store'), [
            'username' => 'jurado1',
            'nombre' => 'Juan',
            'paterno' => 'Pérez',
            'materno' => 'Choque',
            'ci' => '1234567',
            'email' => 'juan@acb.local',
            'rol' => RolEnum::JURADO->value,
            'puede_controlar_tiempo' => 1,
            'password' => 'secreta000',
            'password_confirmation' => 'secreta000',
        ])->assertRedirect(route('admin.usuarios.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'juan@acb.local',
            'username' => 'jurado1',
            'ci' => '1234567',
            'rol' => RolEnum::JURADO->value,
            'puede_controlar_tiempo' => 1,
        ]);

        $usuario = Usuario::where('email', 'juan@acb.local')->first();
        $this->assertNotNull($usuario->email_verified_at);
        $this->assertNotSame('secreta000', $usuario->password);
    }

    #[Test]
    public function no_se_puede_crear_con_email_o_username_duplicados(): void
    {
        Usuario::factory()->create(['email' => 'a@acb.local', 'username' => 'usuariox']);

        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->post(route('admin.usuarios.store'), [
                'nombre' => 'Juan',
                'email' => 'A@acb.local',
                'username' => 'usuariox',
                'rol' => RolEnum::JURADO->value,
                'password' => 'secreta000',
                'password_confirmation' => 'secreta000',
            ])
            ->assertSessionHasErrors(['email', 'username']);
    }

    #[Test]
    public function la_contrasena_es_obligatoria_al_crear(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->post(route('admin.usuarios.store'), [
                'nombre' => 'Juan',
                'email' => 'juan@acb.local',
                'rol' => RolEnum::JURADO->value,
            ])
            ->assertSessionHasErrors('password');
    }

    #[Test]
    public function un_admin_puede_editar_un_usuario(): void
    {
        $admin = $this->crearAdmin();
        $jurado = Usuario::factory()->create(['rol' => RolEnum::JURADO, 'puede_controlar_tiempo' => false]);

        $this->actingAs($admin)->patch(route('admin.usuarios.update', $jurado), [
            'username' => $jurado->username,
            'nombre' => 'María',
            'paterno' => 'López',
            'materno' => null,
            'ci' => null,
            'email' => 'maria@acb.local',
            'rol' => RolEnum::ADMIN->value,
            'puede_controlar_tiempo' => 1,
            'password' => '',
        ])->assertRedirect(route('admin.usuarios.index'));

        $jurado->refresh();

        $this->assertSame('María', $jurado->nombre);
        $this->assertSame('maria@acb.local', $jurado->email);
        $this->assertSame(RolEnum::ADMIN, $jurado->rol);
        $this->assertTrue($jurado->puedeControlarTiempo());
        $this->assertNotNull($jurado->email_verified_at);
    }

    #[Test]
    public function un_admin_no_puede_eliminar_su_propia_cuenta(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->delete(route('admin.usuarios.destroy', $admin))
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    #[Test]
    public function un_admin_no_puede_cambiar_su_propio_rol(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->patch(route('admin.usuarios.update', $admin), [
                'username' => $admin->username,
                'nombre' => $admin->nombre,
                'email' => $admin->email,
                'rol' => RolEnum::JURADO->value,
            ])
            ->assertSessionHasErrors('rol');

        $this->assertSame(RolEnum::ADMIN, $admin->refresh()->rol);
    }

    #[Test]
    public function un_admin_puede_eliminar_a_otro_admin(): void
    {
        $adminA = $this->crearAdmin();
        $adminB = $this->crearAdmin();

        $this->actingAs($adminA)
            ->delete(route('admin.usuarios.destroy', $adminB))
            ->assertRedirect(route('admin.usuarios.index'));

        $this->assertDatabaseMissing('users', ['id' => $adminB->id]);
    }
}