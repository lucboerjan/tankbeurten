@extends('layout.app');

@section('inhoud')
    <div id="mijnFamilieIndex">
        <h1>{{ __('boodschappen.mijnfamilie_titel') }}</h1>
        <hr>

        <div class="row">
            <div class="col-sm-4">
                <h2>{{ __('boodschappen.mijnfamilie_zoekfilter') }}</h2>
            </div>
            <div class="col-sm-8">
                <h2>{{ __('boodschappen.mijnfamilie_zoekresultaat') }}</h2>
                <div id="formulier">

                    <pre class="mermaid">

                        graph TD

GrandPaDad("Camil Boerjan")
GrandMaDad("Helena Van Kerschaever")
GrandPaMom("Louis Vanhulle")
GrandMaMom("Ocatavie Debrouwer")

%% -- parents --
Dad("René Boerjan")
Mom("Martha Vanhulle")

%% -- Children --
Sister1("Anita Boerjan")
Sister2("Claudine Boerjan")
Broer1("Kamiel Boerjan")
Broer2("Frank Boerjan")
Broer3("Geert Boerjan")
Sister3("Fabienne Boerjan")
Me(("Luc Boerjan"))
Sister4("Danielle Boerjan")


%% --- Children --
Kindvriend("Orlando Brasseur")
Kind("Kelly Boerjan")

%% -- Grandchildren --
Kleinkind1("Amélie Brasseur")
Kleinkind2("Aurélie Brasseur")

%% -- Relationships --

subgraph Siblings["Mezelf, broers en zussen"]
    Sister1
    Sister2
    Broer1
    Broer2
    Broer3
    Sister3
    Me
    Sister4
end

subgraph Mother["Moeder"]
    direction LR
    Mom
end


subgraph Father["Moeder"]
    direction LR
    Dad
end



subgraph MomsParents["Grootouders Moeder"]
    direction LR
    GrandPaMom === GrandMaMom
end


subgraph DadsParents["Grootouders Vader"]
    direction LR
    GrandPaDad === GrandMaDad
end

subgraph Children["Kinderen"]
    direction LR
    Kindvriend === Kind
end

subgraph Kleinkinderen["Kleinkinderen"]
    Kleinkind1
    Kleinkind2
end

DadsParents --> Dad
MomsParents --> Mom
Dad --- Siblings
Mom --- Siblings
Me --> Children

Children --> Kleinkinderen
                    </pre>

    <script type="module">
                    import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.esm.min.mjs';
                      mermaid.initialize({ startOnLoad: true });
                    </script>


                </div>
            </div>
        </div>
    </div>
@endsection