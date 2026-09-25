<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Akun;
use App\Models\Pegawai;
use App\Models\Role;
use App\Models\Organization;

class Phase2ATest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Setup base org
        $this->org = Organization::firstOrCreate(['organization_id' => 999], [
            'nama_organisasi' => 'Test Org',
            'kode_organisasi' => 'TEST_ORG',
            'status' => 'active',
            'display_token' => 'test-token'
        ]);

        // Setup Super Admin
        $this->superAdminRole = Role::firstOrCreate(['nama_role' => 'Super Admin'], ['deskripsi' => '']);
        $this->superAdminUser = Akun::create([
            'username' => 'supertest',
            'password' => bcrypt('password'),
            'role_id' => $this->superAdminRole->role_id,
            'role' => 'Super Admin',
        ]);
        
        // Setup User Biasa (with Pegawai)
        $this->userRole = Role::firstOrCreate(['nama_role' => 'Pegawai'], ['deskripsi' => '']);
        
        $this->normalPegawai = Pegawai::create([
            'nip' => 'T001',
            'nama_pegawai' => 'Test Pegawai',
            'email' => 't1@test.com',
            'organization_id' => $this->org->organization_id
        ]);

        $this->normalUser = Akun::create([
            'username' => 'usertest',
            'password' => bcrypt('password'),
            'role_id' => $this->userRole->role_id,
            'role' => 'Pegawai',
            'pegawai_id' => $this->normalPegawai->pegawai_id
        ]);

        // Setup User Biasa (No Pegawai)
        $this->noPegawaiUser = Akun::create([
            'username' => 'nousertest',
            'password' => bcrypt('password'),
            'role_id' => $this->userRole->role_id,
            'role' => 'Pegawai',
            'pegawai_id' => null
        ]);
    }

    public function test_a_super_admin_without_active_organization()
    {
        $response = $this->actingAs($this->superAdminUser)->get('/admin');
        $response->assertRedirect(route('admin.organization.select'));
    }

    public function test_b_super_admin_with_active_organization()
    {
        $response = $this->actingAs($this->superAdminUser)
                         ->withSession(['active_organization_id' => $this->org->organization_id])
                         ->get('/admin');
        $response->assertStatus(200); // Or whatever status dashboard returns, 200 is expected if privileges pass
    }

    public function test_c_super_admin_switch()
    {
        $response = $this->actingAs($this->superAdminUser)
                         ->withSession(['active_organization_id' => $this->org->organization_id])
                         ->post(route('admin.organization.switch'));
        
        $response->assertRedirect(route('admin.organization.select'));
        $this->assertNull(session('active_organization_id'));
    }

    public function test_d_user_biasa()
    {
        $response = $this->actingAs($this->normalUser)->get('/admin');
        // Will it return 200 or 403 because of privilege?
        // Let's assert it doesn't redirect to select organization
        $this->assertNotEquals(route('admin.organization.select'), $response->headers->get('Location'));
    }

    public function test_e_user_biasa_cannot_open_selection()
    {
        $response = $this->actingAs($this->normalUser)->get(route('admin.organization.select'));
        $response->assertStatus(403);
    }

    public function test_f_user_biasa_no_pegawai()
    {
        $response = $this->actingAs($this->noPegawaiUser)->get('/admin');
        $response->assertStatus(403);
    }
}
