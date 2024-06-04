import React, { useEffect, useState } from 'react';
import Highcharts from 'highcharts';
import HighchartsReact from 'highcharts-react-official';
import networkgraph from 'highcharts/modules/networkgraph';

// Initialiser le module networkgraph de Highcharts
networkgraph(Highcharts);

interface WordGraphData {
  'Mots de départ': string[];
  'Liste des mots': string[];
  Distances: {
    [key: string]: string;
  };
}

interface WordGraphProps {
  data: WordGraphData | null;
  key: string; // clé unique pour forcer la recréation du composant
}

const WordGraph: React.FC<WordGraphProps> = ({ data }) => {
  const [chartOptions, setChartOptions] = useState<any>(null);

  useEffect(() => {
    if (data) {
      const nodes = Array.from(new Set([...data['Mots de départ'], ...data['Liste des mots']])).map(word => ({
        id: word,
        name: word,
        marker: {
          radius: 10,
          fillColor: '#87CEEB', // Bleu ciel
          lineWidth: 2,
          lineColor: '#C0C0C0'
        },
        dataLabels: {
          enabled: true,
          format: '{point.name}', // Afficher le nom sur les nœuds
          style: {
            color: '#000000',
            textOutline: 'none'
          },
          allowOverlap: true
        }
      }));

      const links = Object.entries(data.Distances).map(([key, value]) => {
        const [from, to] = key.split('-');
        return {
          from,
          to,
          weight: parseFloat(value)
        };
      });

      const options = {
        chart: {
          type: 'networkgraph',
          plotBorderWidth: 0,
          animation: true
        },
        title: {
          text: ''
        },
        plotOptions: {
          networkgraph: {
            keys: ['from', 'to'],
            layoutAlgorithm: {
              enableSimulation: true,
              linkLength: 150,
              integration: 'verlet'
            },
            link: {
              width: 2,
              color: '#C0C0C0'
            }
          }
        },
        credits: {
          enabled: false
        },
        series: [{
          name: 'K3',
          dataLabels: {
            enabled: true,
            format: '{point.weight}', // Afficher le poids sur les liens
            style: {
              fontSize: '0.8rem',
              fontWeight: 'normal'
            }
          },
          data: links,
          nodes: nodes,
          marker: {
            radius: 10
          },
          node: {
            dataLabels: {
              enabled: true,
              format: '{point.name}', // Afficher le nom sur les nœuds
              style: {
                color: '#000000',
                textOutline: 'none'
              },
              allowOverlap: true
            }
          }
        }]
      };

      setChartOptions(options);
    }
  }, [data]);

  if (!data) {
    return <div>Loading...</div>;
  }

  return (
    <div>
      {chartOptions && (
        <HighchartsReact
          highcharts={Highcharts}
          options={chartOptions}
        />
      )}
    </div>
  );
};

export default WordGraph;
