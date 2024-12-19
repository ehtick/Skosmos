describe('Groups tab', () => {
  it('Loads groups on vocab page', () => {
    // Go to test vocab home page
    cy.visit('/groups/en/')
    // Check that groups tab is available and click it open
    cy.get('#groups').should('not.have.class', 'disabled').click({force: true})
    // Check that groups includes correct top groups
    cy.get('#groups-list li').should('have.length', 3).invoke('text')
      .should('contain', 'Fish')
      .should('contain', 'Freshwater fish')
      .should('contain', 'Saltwater fish')
  })
  it('Loads groups and expands hierarchy on group page', () => {
    // Go to "Freshwater fish" group page
    cy.visit('groups/en/page/?uri=http%3A%2F%2Fwww.skosmos.skos%2Fgroups%2Ffresh')
    // Check that selected element is "Freshwater fish"
    cy.get('#groups-list .selected').should('have.length', 1).invoke('text').should('contain', 'Freshwater fish')
    // Check that "Freshwater fish" has 1 child "Carp"
    cy.get('#groups-list li:has(.selected)').last().find('ul').should('have.length', 1).invoke('text').should('contain', 'Carp')
    // Check that other groups are loaded
    cy.get('#groups-list li').invoke('text')
      .should('contain', 'Fish')
      .should('contain', 'Saltwater fish')
  })
  it('Loads groups on concept page', () => {
    // Go to "Carp" concept page
    cy.visit('/groups/en/page/?uri=http%3A%2F%2Fwww.skosmos.skos%2Fgroups%2Fta112')
    // Check that groups includes correct top groups
    cy.get('#groups-list li').should('have.length', 3).invoke('text')
      .should('contain', 'Fish')
      .should('contain', 'Freshwater fish')
      .should('contain', 'Saltwater fish')
  })
  it('Loads members and hides them on button click', () => {
    // Go to test vocab home page
    cy.visit('/groups/en/')
    // Click groups tab open
    cy.get('#groups').should('not.have.class', 'disabled').click({force: true})
    // Click open button of second group
    cy.get('#groups-list li button').eq(1).click({force: true})
    // Check that child "Carp" is loaded in
    cy.get('#groups-list li ul', {'timeout': 15000}).first().children().should('have.length', 1).invoke('text').should('include', 'Carp')
    // Click open button again
    cy.get('#groups-list li button').eq(1).click({force: true})
    // Check that children are hidden
    cy.get('#groups-list li ul').should('not.exist')
  })
  it('Loads members on group click', () => {
    // Go to test vocab home page
    cy.visit('/groups/en/')
    // Click groups tab open
    cy.get('#groups').should('not.have.class', 'disabled').click({force: true})
    // Click second group
    cy.get('#groups-list li a').eq(1).click({force: true})
    // Check that children are loaded in
    cy.get('#groups-list li ul', {'timeout': 15000}).first().children().should('have.length', 1)
    // Check that child is correct
    cy.get('#groups-list li ul').invoke('text').should('include', 'Carp')
  })
})