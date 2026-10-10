<?php

namespace App\Livewire\Forms;

use App\Models\Club;
use App\Models\User;
use App\Models\User\Role;
use App\Support\ClubLogo;
use Illuminate\Support\Facades\DB;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Form;

/**
 * Formulaire d'un club, partagé par la création et la modification : nom, ville,
 * description et logo.
 */
class ClubForm extends Form
{
    /**
     * Nom du club.
     */
    public string $name = '';

    /**
     * Ville du club (vide si non renseignée).
     */
    public string $city = '';

    /**
     * Jour, heure et lieu des séances (vide si non renseignés).
     */
    public string $description = '';

    /**
     * Nouveau logo envoyé, recadré en carré à l'enregistrement.
     */
    public ?TemporaryUploadedFile $logo = null;

    /**
     * Demande de retrait du logo actuel (modification seulement).
     */
    public bool $removeLogo = false;

    /**
     * Remplit le formulaire avec un club existant.
     */
    public function fillFrom(Club $club): void
    {
        $this->name = $club->name;
        $this->city = $club->city;
        $this->description = $club->description;
    }

    /**
     * Crée le club, avec un code d'invitation et sa section générale, et y inscrit son
     * créateur comme président.
     */
    public function create(User $creator, ClubLogo $club_logo): Club
    {
        $this->normalize();
        $this->validate();

        return DB::transaction(function () use ($creator, $club_logo): Club {
            $club = Club::create([
                'name' => $this->name,
                'code' => Club::generateCode($this->name),
                'city' => $this->city,
                'description' => $this->description,
                'user_id' => $creator->id,
            ]);

            $club->members()->attach($creator, ['role_id' => Role::idFor(Role::PRESIDENT)]);
            $club->createDefaultSection();

            if ($this->logo !== null) {
                $club->update(['logo_path' => $club_logo->store($this->logo, $club)]);
            }

            return $club;
        });
    }

    /**
     * Enregistre les modifications du club ; un nouveau logo ou un retrait supprime l'ancien
     * fichier. Le code d'invitation ne change pas.
     */
    public function update(Club $club, ClubLogo $club_logo): void
    {
        $this->normalize();
        $this->validate();

        DB::transaction(function () use ($club, $club_logo): void {
            $logo_path = $club->logo_path;

            if ($this->logo !== null || $this->removeLogo) {
                $club_logo->delete($club);
                $logo_path = $this->logo !== null ? $club_logo->store($this->logo, $club) : '';
            }

            $club->update([
                'name' => $this->name,
                'city' => $this->city,
                'description' => $this->description,
                'logo_path' => $logo_path,
            ]);
        });

        $this->reset('logo', 'removeLogo');
    }

    /**
     * Règles de validation du formulaire.
     *
     * @return array<string, list<string>>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:50'],
            'city' => ['string', 'max:50'],
            'description' => ['string', 'max:300'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'removeLogo' => ['boolean'],
        ];
    }

    /**
     * Libellés des champs dans les messages de validation.
     *
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'name' => __('clubs.form.fields.name'),
            'city' => __('clubs.form.fields.city'),
            'description' => __('clubs.form.fields.description'),
            'logo' => __('clubs.form.fields.logo'),
        ];
    }

    /**
     * Retire les espaces superflus des champs texte avant validation.
     */
    protected function normalize(): void
    {
        $this->name = trim($this->name);
        $this->city = trim($this->city);
        $this->description = trim($this->description);
    }
}
