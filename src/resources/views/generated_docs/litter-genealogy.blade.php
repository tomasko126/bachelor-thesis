<style>
    #litter-genealogy {
        padding: 3px;
        display: flex;
        transform: scale(0.95);
    }

    #litter-genealogy > #litter-genealogy-container {
        width: 100%;
        align-self: center;
        display: flex;
        flex-direction: column;
    }

    #litter-genealogy-container > header {
        border-bottom: 2px solid #660067;
        text-align: center;
    }

    #litter-genealogy header h1 {
        font-size: 14px;
        margin: 6px;
    }

    #litter-genealogy table {
        border-collapse: collapse;
        height: 100%;
        width: 100%;
    }

    #litter-genealogy table tr {
        height: 35px;
    }

    #litter-genealogy table td {
        width: 150px;
        text-align: center;
        border: 1px solid black;
    }

    #litter-genealogy table td:last-child {
        border-right: unset;
    }

    #litter-genealogy tbody p {
        margin: 0;
    }

    .first .basic-info-section, .first .variety-section, .first .people-section {
        margin-top: 30px;
    }

    .second .basic-info-section, .second .variety-section, .second .people-section {
        margin-top: 10px;
    }

    .fourth .animal-name {
        font-size: 12px;
    }

    .father .animal-name {
        color: #660067;
    }

    .mother .animal-name {
        color: #9a3300;
    }

    .parent {
        font-size: large;
        font-weight: bold;
    }

    #litter-varieties-container {
        border-top: 2px solid #660067;
    }

    #litter-varieties-text {
        margin: 5px;
        text-align: left;
        font-size: 11px;
    }

    .variety {
        font-size: 12px;
    }

    .fourth .variety {
        font-size: 11px;
    }

    .bold {
        font-weight: bold;
    }

    .border {
        border: 1px solid #660067;
    }

    .bottom-border {
        border-bottom: 2px solid #660067;
    }
</style>

<div id="litter-genealogy" class="content border">
    <div id="litter-genealogy-container" class="inner-content border">
        <header>
            <h1 id="litter-registration-number">{{ $registrationNumber }}</h1>
        </header>
        <table>
            <tbody>
            <tr>
                <td id="2" rowspan="8" style="border-left: unset; border-top: unset; height: 90mm;" class="bottom-border father">
                    @isset($genealogy['father']['animal'])
                        <x-first-level-table-cell :animal="$genealogy['father']['animal']"></x-first-level-table-cell>
                    @else
                        <header class="animal-name">
                            Předek neznámý
                        </header>
                    @endisset
                </td>
                <td id="4" rowspan="4" class="father" style="border-top: unset;">
                    @isset($genealogy['father']['father']['animal'])
                        <x-second-level-table-cell :animal="$genealogy['father']['father']['animal']"></x-second-level-table-cell>
                    @else
                        <header class="animal-name">
                            Předek neznámý
                        </header>
                    @endisset
                </td>
                <td id="8" rowspan="2" class="father" style="border-top: unset;">
                    <div class="third-level-container">
                        @isset($genealogy['father']['father']['father']['animal'])
                            <x-third-level-table-cell :animal="$genealogy['father']['father']['father']['animal']"></x-third-level-table-cell>
                        @else
                            <header class="animal-name">
                                Předek neznámý
                            </header>
                        @endisset
                    </div>
                </td>
                <td id="16" class="father" style="border-top: unset;">
                    @isset($genealogy['father']['father']['father']['father']['animal'])
                        <x-fourth-level-table-cell :animal="$genealogy['father']['father']['father']['father']['animal']"></x-fourth-level-table-cell>
                    @else
                        <header class="animal-name" style="font-size: 12px;">
                            Předek neznámý
                        </header>
                    @endisset
                </td>
            </tr>
            <tr>
                <td id="17" class="mother">
                    @isset($genealogy['father']['father']['father']['mother']['animal'])
                        <x-fourth-level-table-cell :animal="$genealogy['father']['father']['father']['mother']['animal']"></x-fourth-level-table-cell>
                    @else
                        <header class="animal-name" style="font-size: 12px;">
                            Předek neznámý
                        </header>
                    @endisset
                </td>
            </tr>
            <tr>
                <td id="9" rowspan="2" class="mother">
                    @isset($genealogy['father']['father']['mother']['animal'])
                        <x-third-level-table-cell :animal="$genealogy['father']['father']['mother']['animal']"></x-third-level-table-cell>
                    @else
                        <header class="animal-name">
                            Předek neznámý
                        </header>
                    @endisset
                </td>
                <td id="18" class="father">
                    @isset($genealogy['father']['father']['mother']['father']['animal'])
                        <x-fourth-level-table-cell :animal="$genealogy['father']['father']['mother']['father']['animal']"></x-fourth-level-table-cell>
                    @else
                        <header class="animal-name" style="font-size: 12px;">
                            Předek neznámý
                        </header>
                    @endisset
                </td>
            </tr>
            <tr>
                <td id="19" class="mother">
                    @isset($genealogy['father']['father']['mother']['mother']['animal'])
                        <x-fourth-level-table-cell :animal="$genealogy['father']['father']['mother']['mother']['animal']"></x-fourth-level-table-cell>
                    @else
                        <header class="animal-name" style="font-size: 12px;">
                            Předek neznámý
                        </header>
                    @endisset
                </td>
            </tr>
            <tr>
                <td id="5" rowspan="4" class="bottom-border mother">
                    @isset($genealogy['father']['mother']['animal'])
                        <x-second-level-table-cell :animal="$genealogy['father']['mother']['animal']"></x-second-level-table-cell>
                    @else
                        <header class="animal-name">
                            Předek neznámý
                        </header>
                    @endisset
                </td>
                <td id="10" rowspan="2" class="father">
                    @isset($genealogy['father']['mother']['father']['animal'])
                        <x-third-level-table-cell :animal="$genealogy['father']['mother']['father']['animal']"></x-third-level-table-cell>
                    @else
                        <header class="animal-name">
                            Předek neznámý
                        </header>
                    @endisset
                </td>
                <td id="20" class="father">
                    @isset($genealogy['father']['mother']['father']['father']['animal'])
                        <x-fourth-level-table-cell :animal="$genealogy['father']['mother']['father']['father']['animal']"></x-fourth-level-table-cell>
                    @else
                        <header class="animal-name" style="font-size: 12px;">
                            Předek neznámý
                        </header>
                    @endisset
                </td>
            </tr>
            <tr>
                <td id="21" class="mother">
                    @isset($genealogy['father']['mother']['father']['mother']['animal'])
                        <x-fourth-level-table-cell :animal="$genealogy['father']['mother']['father']['mother']['animal']"></x-fourth-level-table-cell>
                    @else
                        <header class="animal-name" style="font-size: 12px;">
                            Předek neznámý
                        </header>
                    @endisset
                </td>
            </tr>
            <tr>
                <td id="11" rowspan="2" class="bottom-border mother">
                    @isset($genealogy['father']['mother']['mother']['animal'])
                        <x-third-level-table-cell :animal="$genealogy['father']['mother']['mother']['animal']"></x-third-level-table-cell>
                    @else
                        <header class="animal-name">
                            Předek neznámý
                        </header>
                    @endisset
                </td>
                <td id="22" class="father">
                    @isset($genealogy['father']['mother']['mother']['father']['animal'])
                        <x-fourth-level-table-cell :animal="$genealogy['father']['mother']['mother']['father']['animal']"></x-fourth-level-table-cell>
                    @else
                        <header class="animal-name" style="font-size: 12px;">
                            Předek neznámý
                        </header>
                    @endisset
                </td>
            </tr>
            <tr>
                <td id="23" class="bottom-border mother">
                    @isset($genealogy['father']['mother']['mother']['mother']['animal'])
                        <x-fourth-level-table-cell :animal="$genealogy['father']['mother']['mother']['mother']['animal']"></x-fourth-level-table-cell>
                    @else
                        <header class="animal-name" style="font-size: 12px;">
                            Předek neznámý
                        </header>
                    @endisset
                </td>
            </tr>
            <tr>
                <td id="3" rowspan="8" style="border-left: unset; border-bottom: unset; height: 90mm;" class="bottom-border mother">
                    @isset($genealogy['mother']['animal'])
                        <x-first-level-table-cell :animal="$genealogy['mother']['animal']"></x-first-level-table-cell>
                    @else
                        <header class="animal-name">
                            Předek neznámý
                        </header>
                    @endisset
                </td>
                <td id="6" rowspan="4" class="father">
                    @isset($genealogy['mother']['father']['animal'])
                        <x-second-level-table-cell :animal="$genealogy['mother']['father']['animal']"></x-second-level-table-cell>
                    @else
                        <header class="animal-name">
                            Předek neznámý
                        </header>
                    @endisset
                </td>
                <td id="12" rowspan="2" class="father">
                    @isset($genealogy['mother']['father']['father']['animal'])
                        <x-third-level-table-cell :animal="$genealogy['mother']['father']['father']['animal']"></x-third-level-table-cell>
                    @else
                        <header class="animal-name">
                            Předek neznámý
                        </header>
                    @endisset
                </td>
                <td id="24" class="father">
                    @isset($genealogy['mother']['father']['father']['father']['animal'])
                        <x-fourth-level-table-cell :animal="$genealogy['mother']['father']['father']['father']['animal']"></x-fourth-level-table-cell>
                    @else
                        <header class="animal-name" style="font-size: 12px;">
                            Předek neznámý
                        </header>
                    @endisset
                </td>
            </tr>
            <tr>
                <td id="25" class="mother">
                    @isset($genealogy['mother']['father']['father']['mother']['animal'])
                        <x-fourth-level-table-cell :animal="$genealogy['mother']['father']['father']['mother']['animal']"></x-fourth-level-table-cell>
                    @else
                        <header class="animal-name" style="font-size: 12px;">
                            Předek neznámý
                        </header>
                    @endisset
                </td>
            </tr>
            <tr>
                <td id="13" rowspan="2" class="mother">
                    @isset($genealogy['mother']['father']['mother']['animal'])
                        <x-third-level-table-cell :animal="$genealogy['mother']['father']['mother']['animal']"></x-third-level-table-cell>
                    @else
                        <header class="animal-name">
                            Předek neznámý
                        </header>
                    @endisset
                </td>
                <td id="26" class="father">
                    @isset($genealogy['mother']['father']['mother']['father']['animal'])
                        <x-fourth-level-table-cell :animal="$genealogy['mother']['father']['mother']['father']['animal']"></x-fourth-level-table-cell>
                    @else
                        <header class="animal-name" style="font-size: 12px;">
                            Předek neznámý
                        </header>
                    @endisset
                </td>
            </tr>
            <tr>
                <td id="27" class="mother">
                    @isset($genealogy['mother']['father']['mother']['mother']['animal'])
                        <x-fourth-level-table-cell :animal="$genealogy['mother']['father']['mother']['mother']['animal']"></x-fourth-level-table-cell>
                    @else
                        <header class="animal-name" style="font-size: 12px;">
                            Předek neznámý
                        </header>
                    @endisset
                </td>
            </tr>
            <tr>
                <td id="7" rowspan="4" class="bottom-border mother" style="border-bottom: unset;">
                    @isset($genealogy['mother']['mother']['animal'])
                        <x-second-level-table-cell :animal="$genealogy['mother']['mother']['animal']"></x-second-level-table-cell>
                    @else
                        <header class="animal-name">
                            Předek neznámý
                        </header>
                    @endisset
                </td>
                <td id="14" rowspan="2" class="father">
                    @isset($genealogy['mother']['mother']['father']['animal'])
                        <x-third-level-table-cell :animal="$genealogy['mother']['mother']['father']['animal']"></x-third-level-table-cell>
                    @else
                        <header class="animal-name">
                            Předek neznámý
                        </header>
                    @endisset
                </td>
                <td id="28" class="father">
                    @isset($genealogy['mother']['mother']['father']['father']['animal'])
                        <x-fourth-level-table-cell :animal="$genealogy['mother']['mother']['father']['father']['animal']"></x-fourth-level-table-cell>
                    @else
                        <header class="animal-name" style="font-size: 12px;">
                            Předek neznámý
                        </header>
                    @endisset
                </td>
            </tr>
            <tr>
                <td id="29" class="mother">
                    @isset($genealogy['mother']['mother']['father']['mother']['animal'])
                        <x-fourth-level-table-cell :animal="$genealogy['mother']['mother']['father']['mother']['animal']"></x-fourth-level-table-cell>
                    @else
                        <header class="animal-name" style="font-size: 12px;">
                            Předek neznámý
                        </header>
                    @endisset
                </td>
            </tr>
            <tr>
                <td id="15" rowspan="2" class="bottom-border mother" style="border-bottom: unset;">
                    @isset($genealogy['mother']['mother']['mother']['animal'])
                        <x-third-level-table-cell :animal="$genealogy['mother']['mother']['mother']['animal']"></x-third-level-table-cell>
                    @else
                        <header class="animal-name">
                            Předek neznámý
                        </header>
                    @endisset
                </td>
                <td id="30" class="father">
                    @isset($genealogy['mother']['mother']['mother']['father']['animal'])
                        <x-fourth-level-table-cell :animal="$genealogy['mother']['mother']['mother']['father']['animal']"></x-fourth-level-table-cell>
                    @else
                        <header class="animal-name" style="font-size: 12px;">
                            Předek neznámý
                        </header>
                    @endisset
                </td>
            </tr>
            <tr>
                <td id="31" class="bottom-border mother" style="border-bottom: unset;">
                    @isset($genealogy['mother']['mother']['mother']['mother']['animal'])
                        <x-fourth-level-table-cell :animal="$genealogy['mother']['mother']['mother']['mother']['animal']"></x-fourth-level-table-cell>
                    @else
                        <header class="animal-name" style="font-size: 12px;">
                            Předek neznámý
                        </header>
                    @endisset
                </td>
            </tr>
            </tbody>
        </table>
        <div id="litter-varieties-container">
            <p class="bold" id="litter-varieties-text">
                Varietnost vrhu: {{ $varieties }}
            </p>
        </div>
    </div>
</div>
