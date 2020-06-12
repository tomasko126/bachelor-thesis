<style>
    #animal-overview {
        padding: 3px;
        display: flex;
        transform: scale(0.95);
    }

    #animal-overview.portrait {
        height: 294mm;
    }

    #animal-overview.landscape {
        height: 205mm;
    }

    #animal-overview-container {
        width: 100%;
        height: 100%;
        align-self: center;
        display: flex;
        flex-direction: column;
    }

    .border {
        border: 1px solid #660067;
    }

    #animal-overview header {
        display: flex;
        justify-content: center;
    }

    .header-main {
        margin: 5px;
        font-size: 18px;
        text-align: center;
    }

    .header-detail {
        margin-top: 12px;
        font-size: 20px;
    }

    .document-type {
        font-size: 35px;
        color: #9a3300;
        text-transform: uppercase;
    }

    #top-images {
        display: none;
        justify-content: space-around;
        margin-bottom: 20px;
    }

    #top-images.portrait {
        display: flex;
    }

    #animal-overview main {
        display: flex;
        height: 100%;
    }

    #animal-overview aside {
        width: 35%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: space-between;
    }

    #animal-overview aside.portrait {
        opacity: 0;
        width: 10%;
    }

    #image-description {
        text-transform: uppercase;
    }

    #animal-overview article {
        display: flex;
        justify-content: space-between;
        flex-direction: column;
        width: 65%;
    }

    #animal-overview article.portrait {
        width: 100%;
    }

    #animal-info {
        empty-cells: show;
    }

    #animal-info tr td:first-child {
        text-align: right;
        padding-right: 25px;
    }

    #animal-info tr td:nth-child(2) {
        font-weight: bold;
    }

    .first .animal-name {
        font-size: 20px;
    }

    #breeding-info {
        color: #66006b;
        text-align: center;
        text-transform: uppercase;
        margin: 20px 0;
    }

    #breeding-info > .breeding_limitation {
        word-break: break-all;
    }

    #registrations {
        display: flex;
        justify-content: space-between;
    }

    #registrations.portrait {
        justify-content: flex-end;
    }

    #registrations > section {
        border: 1px solid black;
        border-bottom: unset;
        height: 230px;
        width: 320px;
    }

    #registrations td:first-child {
        padding-right: 35px;
    }

    #litter-registration.portrait {
        display: none;
    }

    #animal-registration {
        border-right: unset !important;
    }

    .registration-header {
        border-bottom: 1px solid black;
        text-transform: uppercase;
        color: #9a3300;
        padding: 2px;
        margin-bottom: 10px;
    }

    .registration-header > span {
        text-transform: none;
    }

    .bold {
        font-weight: bold;
    }

    #confirmation {
        display: flex;
        width: 70%;
        float: right;
        justify-content: space-evenly;
    }

    #confirmation-text {
        display: flex;
        flex-direction: column-reverse;
    }

    #circle {
        border-radius: 40px;
        width: 80px;
        height: 80px;
        border: 1px solid black;
    }
</style>

<div id="animal-overview" class="border {{ $orientation }}">
    <div id="animal-overview-container" class="border">
        <header>
            <h1 class="header-main">
                Chovatelská stanice {{ $station() }} vydává pod záštitou Českého klubu potkanů
                <p class="header-detail">
                    <span class="document-type">{{ $documentType }}</span>
                    potkana laboratorního
                </p>
            </h1>
        </header>

        <section id="top-images" class="{{ $orientation }}">
            <img src="/img/pdf_logo.png" style="opacity: 0;"/>
            <img src="/img/pdf_logo.png" style="width: 5cm; height: 5cm;"/>
        </section>

        <main>
            <aside class="{{ $orientation }}">
                <img src="/img/pdf_logo.png" />
                <div style="text-align: center;">
                    <p id="image-description" class="bold">Zákres rozložení znaků</p>
                    <img src="/img/pdf_potkan.png" />
                </div>
            </aside>
            <article class="{{ $orientation }}">
                <section>
                    <table id="animal-info">
                        <tr>
                            <td>Jméno potkana</td>
                            <td class="animal-name">{{ $animal->name }}</td>
                        </tr>
                        <tr>
                            <td>Pohlaví</td>
                            <td>{{ $animal->sex }}</td>
                        </tr>
                        <tr>
                            <td>Datum narození</td>
                            <td>{{ $animal->birthdate }}</td>
                        </tr>
                        <tr>
                            <td>Uši</td>
                            <td>{{ $animal->ear_type }}</td>
                        </tr>
                        <tr>
                            <td>Srst</td>
                            <td>{{ $animal->fur_type }}</td>
                        </tr>
                        <tr>
                            <td>Barva</td>
                            <td>{{ $animal->fur_color }}</td>
                        </tr>
                        <tr>
                            <td>Znaky</td>
                            <td>{{ $animal->markings }}</td>
                        </tr>
                        <tr>
                            <td>Barva očí</td>
                            <td>{{ $animal->eyes_color }}</td>
                        </tr>
                        <tr>
                            <td>Odchovaná mláďata</td>
                            <td>
                                {{ isset($litter->babies_reared) ? $litter->babies_reared : '-' }} / {{ isset($litter->babies_born) ? $litter->babies_born : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td>Poměr pohlaví</td>
                            <td>
                                {{ isset($litter->reared_boys) ? $litter->reared_boys : '-' }} M / {{ isset($litter->reared_girls) ? $litter->reared_girls : '-' }} F
                            </td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td>Chovatel</td>
                            <td>{{ isset($litter) ? $litter->breeder_name : '' }}</td>
                        </tr>
                        <tr>
                            <td>Kontakt</td>
                            <td>{{ isset($litter) ? $litter->breeder_contact : '' }}</td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td>Majitel</td>
                            <td>{{ $animal->owner_name }}</td>
                        </tr>
                        <tr>
                            <td>Kontakt</td>
                            <td>{{ $animal->owner_contact }}</td>
                        </tr>
                        <tr>
                            <td>Číslo členského průkazu</td>
                            <td>{{ $animal->owner_member_card_number }}</td>
                        </tr>
                    </table>
                </section>

                <section id="breeding-info" class="bold">
                        <span class="breeding_available">
                            @isset($isAnimalAvailableForBreeding)
                                {{ $isAnimalAvailableForBreeding ? 'Chov povolen' : 'Chov není povolen' }}
                            @endisset
                        </span>
                    <p class="breeding_limitation">
                        @isset($animalBreedingLimitation)
                            {{ !empty($animalBreedingLimitation()) ? ('Poznámka: ' . $animalBreedingLimitation) : '' }}
                        @endisset
                    </p>
                </section>

                <div id="registrations" class="{{ $orientation }}">
                    <section id="litter-registration" class="{{ $orientation }}">
                        <header class="registration-header bold">
                            Registrace vrhu&nbsp;<span>{{ $registrationHeaderType }}</span>
                        </header>

                        <table>
                            <tr>
                                <td>datum vyplnění</td>
                                <td>{{ $litterApprovalRequestCreated ?? '' }}</td>
                            </tr>
                            <tr>
                                <td>datum schválení</td>
                                <td>{{ $litterApprovalRequestApproved }}</td>
                            </tr>
                            <tr>
                                <td>registrační číslo</td>
                                <td class="bold">{{ $litterApprovalRequestRegistrationNumber }}</td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td>bezpečnostní kolek</td>
                            </tr>
                        </table>
                        <div id="confirmation">
                            <div id="confirmation-text">
                                <p>© ČKP z.s</p>
                            </div>
                            <div id="circle"></div>
                        </div>
                    </section>
                    <section id="animal-registration">
                        <header class="registration-header bold">
                            Registrace potkana
                        </header>

                        <table>
                            <tr>
                                <td>datum registrace</td>
                                <td>{{ $animalRegistrationDate }}</td>
                            </tr>
                            <tr>
                                <td>registrační číslo</td>
                                <td>{{ $animalRegistrationNumber }}</td>
                            </tr>
                            <tr>
                                <td>poznámka</td>
                                <td>{{ $animalRegistrationNote }}</td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td>registroval</td>
                                <td>{{ $animalRegistrationRegistrator }}</td>
                            </tr>
                        </table>
                    </section>
                </div>
            </article>
        </main>
    </div>
</div>
