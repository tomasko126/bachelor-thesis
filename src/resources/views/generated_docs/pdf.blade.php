<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <style>
        html, body {
            font-family: sans-serif;
            font-size: 14px;
            display: flex;
            width: 100%;
            height: 100%;
            justify-content: center;
            margin: 40px 0;
        }
    </style>
    <script src="/js/html2pdf.js"></script>
</head>
<body>
    <div>
        <x-animal-overview :animal="$animal" :litter="$litter" :orientation="$orientation"></x-animal-overview>
        <x-litter-genealogy :genealogy="$genealogy" :litter="$litter"></x-litter-genealogy>
    </div>

    <script>
        // Set options for both pages
        var animalOverviewOptions = {
            filename: 'průkaz.pdf',
            image: { type: 'jpeg', quality: 1 },
            jsPDF: { format: 'a4', orientation: '{{ $orientation }}' }
        };

        var litterGenealogyOptions = {
            filename: 'průkaz.pdf',
            image: { type: 'jpeg', quality: 1 },
            jsPDF: { format: 'a4', orientation: 'landscape' }
        };

        // Export animal overview and litter genealogy
        html2pdf().set(animalOverviewOptions).from(document.getElementById('animal-overview')).toPdf().get('pdf').then(function (pdf) {
            pdf.addPage('a4', 'landscape');
        }).set(litterGenealogyOptions).from(document.getElementById('litter-genealogy')).toContainer().toCanvas().toPdf().save();
    </script>
</body>
</html>
