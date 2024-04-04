function splitDataSetBy(data, key) {
    return data.reduce((acc, record) => {
        const dataset = acc.find(d=>d.label === record[key])
        if(!dataset) {
            acc.push({
                label: record[key],
                data: [{x: `${record.time_unit}`, y: record.downloads}]
            })
            return acc
        }
        dataset.data.push({x: `${record.time_unit}`, y: record.downloads})
        return acc
    }, [])
}
function downloadsPerDayChart(element) {
    const data = Alpine.store('reports').downloadsPerDay.stats
    return new Chart(element, {
        type: 'bar',
        options: {
            aspectRatio: 2,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Download count'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: `${Alpine.store('reports').downloadsPerDay.filters.timeUnit}s`
                    }
                }
            }
        },
        data: {
            datasets: [
                ...splitDataSetBy(data, 'value')
            ]
        }
    })
}
function downloadsPerBusinessChart(element) {
    const data = Alpine.store('reports').downloadsPerBusiness.stats
    return new Chart(element, {
        type: 'bar',
        options: {
            aspectRatio: 2,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Download count'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: `Business Type`
                    }
                }
            }
        },
        data: {
            labels: data?.map(r=>r.business_type),
            datasets: [
                {
                    label: "Downloads Per Business Type",
                    data: data?.map(r=>r.downloads)
                }
            ]
        }
    })
}