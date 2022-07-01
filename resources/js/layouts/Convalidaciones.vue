<template>
	<div>
		<v-card>
			<v-card-actions>
				<v-row>
					<v-col cols="12" md="6" lg="6">
						<v-row>
							<v-col cols="12" md="12" lg="12">
								<v-select label="Modulo A"> </v-select>
							</v-col>
						</v-row>
						<v-row>
							<v-col cols="12" md="12" lg="12">
								<v-list shaped rounded dense style="max-height: 225px" class="overflow-y-auto">
									<v-subheader>Temas</v-subheader>
									<v-list-item-group>
										<v-list-item two-line>
											<v-list-item-content>
												<v-list-item-title>1</v-list-item-title>
												<v-list-item-subtitle>2</v-list-item-subtitle>
											</v-list-item-content>
										</v-list-item>
									</v-list-item-group>
								</v-list>
							</v-col>
						</v-row>
					</v-col>
				</v-row>
			</v-card-actions>
		</v-card>
	</div>
</template>