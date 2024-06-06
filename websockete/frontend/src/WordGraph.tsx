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
          lineColor: '#C0C0C0',
          states: {
            hover: {
              radius: 15,
              lineWidth: 3,
              fillColor: '#1E90FF' // Bleu dodger
            }
          }
        },
        dataLabels: {
          enabled: true,
          format: '{point.name}', // Afficher le nom sur les nœuds
          style: {
            color: '#FFFFFF', // Blanc
            textOutline: 'none',
            fontSize: '17px'
          },
          allowOverlap: true
        }
      }));

      const links = Object.entries(data.Distances).map(([key, value]) => {
        const [from, to] = key.split('-');
        return {
          from,
          to,
          weight: parseFloat(value),
          color: '#B0C4DE', // Bleu clair
          width: 2
        };
      });

      const options = {
        chart: {
          type: 'networkgraph',
          plotBorderWidth: 0,
          animation: true,
          backgroundColor: 'transparent'
        },
        title: {
          text: '',
          style: {
            display: 'none'
          }
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
              color: '#C0C0C0',
              width: 2,
              states: {
                hover: {
                  color: '#FF6347', // Tomate
                  width: 3
                }
              },
              dataLabels: {
                enabled: true,
                format: '{point.weight}', // Afficher le poids sur les liens
                style: {
                  fontSize: '15px',
                  fontWeight: 'normal',
                  color: '#FFFFFF' // Blanc
                }
              }
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
              fontSize: '12px',
              fontWeight: 'normal',
              color: '#FFFFFF' // Blanc
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
                color: '#FFFFFF', // Blanc
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
    <div className="HighchartsContainer">
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
