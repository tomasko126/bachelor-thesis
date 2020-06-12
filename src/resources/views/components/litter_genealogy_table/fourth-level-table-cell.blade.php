@include('components.litter_genealogy_table.tablefunctions')

<section class="fourth">
    <header class="animal-name">
        {{ getName($animal) }}
    </header>
    <div class="variety-section">
        <p class="variety">
            {{ getVariety($animal, 0) }}
        </p>
        <p class="variety">
            {{ getVariety($animal, 1) }}
        </p>
    </div>
</section>
