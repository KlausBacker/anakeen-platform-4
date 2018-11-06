<template>
    <div class="smart-structure-hierarchy">
        <svg class="smart-structure-hierarchy-graph" ref="treeSvg">
            <defs>
                <marker id="arrowhead" viewBox="0 0 10 10" refX="10" refY="5" markerWidth="10" markerHeight="10" orient="auto-start-reverse">
                    <path d="M 0 0 L 10 5 L 0 10 z" />
                </marker>
            </defs>
        </svg>
    </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">

</style>
<!-- Global CSS -->
<style lang="scss">
    .smart-structure-hierarchy {
        width: 100%;
        height: 100%;
        .smart-structure-hierarchy-graph {
            width: 100%;
            height: 100%;

            .node rect {
                cursor: pointer;
                fill: #fff;
                fill-opacity: 0.5;
                stroke: #3182bd;
                stroke-width: 1.5px;
            }

            .node text {
                font: 10px sans-serif;
                pointer-events: none;
            }

            .link {
                fill: none;
                stroke: #9ecae1;
                stroke-width: 1.5px;
            }

        }
    }
</style>
<script>
    import * as d3 from "d3";
    // Creates a curved (diagonal) path from parent to the child nodes
    const diagonal = d3.linkHorizontal().x(d => d.y).y(d => d.x);
    const color = (vm) => (d) => {
      if (d.data.name === vm.currentStructure) {
        return "#fd8d3c";
      }
      return "#c6dbef";
    }
    export default {
        props: {
          data: {
            type: Array,
            default: () => ([])
          },
          currentStructure: {
            type: String,
            required: true
          }
        },
      mounted() {
        this.initGraph(this.$refs.treeSvg);
        if (!this.empty) {
          this.normalizeData();
          this.updateGraph(this.root);
        }
      },
      updated() {
        if (!this.empty) {
          this.normalizeData();
          this.resizeAndUpdateTree(this.$refs.treeSvg);
        }
      },
      beforeDestroy() {
        window.removeEventListener('resize', () => {
          this.resizeAndUpdateTree(this.$refs.treeSvg);
        });
      },
      data() {
        return {
          root: {},
          tree: {},
          svg: {},
          duration: 0,
          i: 0,
        };
      },
      computed: {
        empty() {
          return !this.data || !this.data.length;
        },
        dataEmptyText() {
          return this.emptyText || '';
        },
      },
      methods: {
        normalizeData() {
          this.reinitNodes();
          if (!this.empty) {
            this.root = this.data[0];
          }
          this.root = d3.hierarchy(this.root, d => d.children);
          this.root.x0 = 50;
          this.root.y0 = 10;
        },
        reinitNodes() {
          this.root = {
            name: 'Hierarchy',
            x0: 50,
            y0: 10,
            children: [],
          };
        },
        initGraph(element) {
          let { width, height } = this.$el.getBoundingClientRect();
          console.log(width, height);
          const margin = { top: 0, right: 90, bottom: 90, left: 90 };
          width = width - margin.left - margin.right;
          height = height - margin.top - margin.bottom;

          // append the svg object to the body of the page
          // appends a 'group' element to 'svg'
          // moves the 'group' element to the top left margin
          let translateValue = `translate(${margin.left}, 0)`;
          this.svg = d3.select(element)
            .attr('width', width + margin.left)
            .attr('height', height + margin.bottom)
            .call(d3.zoom().scaleExtent([0, 100]).on('zoom', () => {
              this.svg.attr('transform', `translate( ${d3.event.transform.x},${d3.event.transform.y} )`);
            }))
            .on('dblclick.zoom', null)
            .append('g')
            .attr('transform', translateValue);

          const realSize = {
            width: height,
            height: width,
          };

          // declares a tree layout and assigns the size
          this.tree = d3.tree().size([realSize.width, realSize.height]);
          window.addEventListener('resize', () => {
            this.resizeAndUpdateTree(this.$refs.treeSvg);
          });
        },
        updateGraph(source) {
          // Compute the flattened node list.
          var nodes = this.root.descendants();


          // Compute the "layout". TODO https://github.com/d3/d3-hierarchy/issues/67
          var index = -1;
          this.root.eachBefore(function(n) {
            ++index;
            n.x = (index * 25) + 20;
            n.y = n.depth * 50;
          });

          // Update the nodes…
          var node = this.svg.selectAll(".node")
            .data(nodes, function(d) {
              this.i += 1;
              return d.id || (d.id = this.i);
            });

          var nodeEnter = node.enter().append("g")
            .attr("class", "node")
            .attr("transform", function(d) { return "translate(" + source.y0 + "," + source.x0 + ")"; })
            .style("opacity", 0)
            .on("click", this.onClickNode)

          // Enter any new nodes at the parent's previous position.
          nodeEnter.append("rect")
            .attr("y", -20 / 2)
            .attr("height", 20)
            .attr("width", (d) => {
              const value = d.data.name;
              return `${value.split("").length}rem`;
            })
            .style("fill", color(this));

          nodeEnter.append("text")
            .attr("dy", 3.5)
            .attr("dx", 5.5)
            .text(function(d) { return d.data.name; });

          // Transition nodes to their new position.
          nodeEnter.transition()
            .duration(this.duration)
            .attr("transform", function(d) { return "translate(" + d.y + "," + d.x + ")"; })
            .style("opacity", 1);

          node.transition()
            .duration(this.duration)
            .attr("transform", function(d) { return "translate(" + d.y + "," + d.x + ")"; })
            .style("opacity", 1)
            .select("rect")
            .style("fill", color(this));

          // Transition exiting nodes to the parent's new position.
          node.exit().transition()
            .duration(this.duration)
            .attr("transform", function(d) { return "translate(" + source.y + "," + source.x + ")"; })
            .style("opacity", 0)
            .remove();

          // Update the links…
          var link = this.svg.selectAll(".link")
            .data(this.root.links(), function(d) { return d.target.id; });

          // Enter any new links at the parent's previous position.
          link.enter().insert("path", "g")
            .attr("class", "link")
            .attr("d", function(d) {
              var o = {x: source.x0, y: source.y0};
              return diagonal({source: o, target: o});
            })
            .transition()
            .duration(this.duration)
            .attr("d", diagonal);

          // Transition links to their new position.
          link.transition()
            .duration(this.duration)
            .attr("d", diagonal);

          // Transition exiting nodes to the parent's new position.
          link.exit().transition()
            .duration(this.duration)
            .attr("d", function(d) {
              var o = {x: source.x, y: source.y};
              return diagonal({source: o, target: o});
            })
            .remove();

          // Stash the old positions for transition.
          this.root.each(function(d) {
            d.x0 = d.x;
            d.y0 = d.y;
          });
        },
        onClickNode(d) {
          console.log(d);
          this.$router.push({ name: "SmartStructures::name", params: { ssName: d.data.name }})
        },
        resizeAndUpdateTree(element) {
          if (element) {
            const { width, height } = this.$el.getBoundingClientRect();
            const realSize = {
              width: height,
              height: width,
            };
            this.tree.size([realSize.width, realSize.height]);
            this.svg.attr('width', width).attr('height', height);
            if (!this.empty) {
              this.updateGraph(this.root);
            }
          }
        },
      },
    }
</script>
