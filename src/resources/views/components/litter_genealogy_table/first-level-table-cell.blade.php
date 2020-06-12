@include('components.litter_genealogy_table.tablefunctions')

<section class="first">
    <header class="animal-name parent">
        {{ getName($animal) }}
    </header>
    <div class="basic-info-section">
        <p>RČ {{ getRegistrationNumber($animal) }}</p>
        <p>* {{ getBirthDate($animal) }} </p>
    </div>
    <div class="variety-section">
        <p class="variety">
            {{ getVariety($animal, 0) }}
        </p>
        <p class="variety">
            {{ getVariety($animal, 1) }}
        </p>
    </div>
    <div class="people-section">
        <p>Majitel: {{ getOwner($animal) }} </p>
        <p>Chovatel: {{ getBreeder($animal) }} </p>
    </div>
</section>
